<?php

namespace App\EInvoice\Services;

use App\EInvoice\Models\EInvoice;
use DOMDocument;
use DOMElement;

class EInvoiceXmlService
{
    /**
     * Generate UBL-TR XML for E-Invoice.
     *
     * @param  EInvoice  $eInvoice  E-Invoice model
     * @return string UBL-TR XML content
     */
    public function generateXml(EInvoice $eInvoice): string
    {
        $xml = new DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        // Root element
        $invoice = $xml->createElement('Invoice');
        $invoice->setAttribute('xmlns', 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2');
        $invoice->setAttribute('xmlns:cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $invoice->setAttribute('xmlns:cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $xml->appendChild($invoice);

        // UUID
        $uuid = $xml->createElement('cbc:UUID', $eInvoice->invoice_uuid);
        $invoice->appendChild($uuid);

        // Invoice Number
        $id = $xml->createElement('cbc:ID', $eInvoice->invoice_number);
        $invoice->appendChild($id);

        // Issue Date
        $issueDate = $xml->createElement('cbc:IssueDate', $eInvoice->invoice_date->format('Y-m-d'));
        $invoice->appendChild($issueDate);

        // Invoice Type Code
        $typeCode = $xml->createElement('cbc:InvoiceTypeCode', $this->getInvoiceTypeCode($eInvoice->invoice_type));
        $invoice->appendChild($typeCode);

        // Document Currency Code
        $currency = $xml->createElement('cbc:DocumentCurrencyCode', $eInvoice->currency);
        $invoice->appendChild($currency);

        // Supplier (Seller)
        $this->addSupplierParty($xml, $invoice, $eInvoice);

        // Customer (Buyer)
        $this->addCustomerParty($xml, $invoice, $eInvoice);

        // Line Items
        $this->addInvoiceLines($xml, $invoice, $eInvoice);

        // Totals
        $this->addMonetaryTotals($xml, $invoice, $eInvoice);

        return $xml->saveXML();
    }

    /**
     * Add supplier party to XML.
     *
     * @param  DOMDocument  $xml  XML document
     * @param  DOMElement  $invoice  Invoice element
     * @param  EInvoice  $eInvoice  E-Invoice model
     */
    protected function addSupplierParty(DOMDocument $xml, $invoice, EInvoice $eInvoice): void
    {
        $company = $eInvoice->company;

        $supplierParty = $xml->createElement('cac:AccountingSupplierParty');

        $party = $xml->createElement('cac:Party');

        // Party Name
        $partyName = $xml->createElement('cac:PartyName');
        $name = $xml->createElement('cbc:Name', $company->name ?? '');
        $partyName->appendChild($name);
        $party->appendChild($partyName);

        // Postal Address
        $postalAddress = $xml->createElement('cac:PostalAddress');
        $cityName = $xml->createElement('cbc:CityName', $company->city ?? 'İstanbul');
        $country = $xml->createElement('cac:Country');
        $countryCode = $xml->createElement('cbc:IdentificationCode', 'TR');
        $country->appendChild($countryCode);
        $postalAddress->appendChild($cityName);
        $postalAddress->appendChild($country);
        $party->appendChild($postalAddress);

        // Tax Scheme
        $taxScheme = $xml->createElement('cac:PartyTaxScheme');
        $taxId = $xml->createElement('cbc:CompanyID', $company->tax_number ?? '');
        $taxScheme->appendChild($taxId);
        $party->appendChild($taxScheme);

        $supplierParty->appendChild($party);
        $invoice->appendChild($supplierParty);
    }

    /**
     * Add customer party to XML.
     *
     * @param  DOMDocument  $xml  XML document
     * @param  DOMElement  $invoice  Invoice element
     * @param  EInvoice  $eInvoice  E-Invoice model
     */
    protected function addCustomerParty(DOMDocument $xml, $invoice, EInvoice $eInvoice): void
    {
        $customerParty = $xml->createElement('cac:AccountingCustomerParty');

        $party = $xml->createElement('cac:Party');

        // Party Name
        $partyName = $xml->createElement('cac:PartyName');
        $name = $xml->createElement('cbc:Name', $eInvoice->customer_name);
        $partyName->appendChild($name);
        $party->appendChild($partyName);

        // Tax Scheme
        if ($eInvoice->customer_tax_number) {
            $taxScheme = $xml->createElement('cac:PartyTaxScheme');
            $taxId = $xml->createElement('cbc:CompanyID', $eInvoice->customer_tax_number);
            $taxScheme->appendChild($taxId);
            $party->appendChild($taxScheme);
        }

        $customerParty->appendChild($party);
        $invoice->appendChild($customerParty);
    }

    /**
     * Add invoice lines to XML.
     *
     * @param  DOMDocument  $xml  XML document
     * @param  DOMElement  $invoice  Invoice element
     * @param  EInvoice  $eInvoice  E-Invoice model
     */
    protected function addInvoiceLines(DOMDocument $xml, $invoice, EInvoice $eInvoice): void
    {
        $line = $xml->createElement('cac:InvoiceLine');

        $id = $xml->createElement('cbc:ID', '1');
        $line->appendChild($id);

        $quantity = $xml->createElement('cbc:InvoicedQuantity', '1');
        $quantity->setAttribute('unitCode', 'C62');
        $line->appendChild($quantity);

        $lineAmount = $xml->createElement('cbc:LineExtensionAmount', (string) $eInvoice->total_amount);
        $lineAmount->setAttribute('currencyID', $eInvoice->currency);
        $line->appendChild($lineAmount);

        $item = $xml->createElement('cac:Item');
        $description = $xml->createElement('cbc:Description', 'Navlun Bedeli');
        $item->appendChild($description);
        $line->appendChild($item);

        $price = $xml->createElement('cac:Price');
        $priceAmount = $xml->createElement('cbc:PriceAmount', (string) $eInvoice->total_amount);
        $priceAmount->setAttribute('currencyID', $eInvoice->currency);
        $price->appendChild($priceAmount);
        $line->appendChild($price);

        $invoice->appendChild($line);
    }

    /**
     * Add monetary totals to XML.
     *
     * @param  DOMDocument  $xml  XML document
     * @param  DOMElement  $invoice  Invoice element
     * @param  EInvoice  $eInvoice  E-Invoice model
     */
    protected function addMonetaryTotals(DOMDocument $xml, $invoice, EInvoice $eInvoice): void
    {
        $total = $xml->createElement('cac:LegalMonetaryTotal');

        $lineExtension = $xml->createElement('cbc:LineExtensionAmount', (string) $eInvoice->total_amount);
        $lineExtension->setAttribute('currencyID', $eInvoice->currency);
        $total->appendChild($lineExtension);

        $taxExclusive = $xml->createElement('cbc:TaxExclusiveAmount', (string) $eInvoice->total_amount);
        $taxExclusive->setAttribute('currencyID', $eInvoice->currency);
        $total->appendChild($taxExclusive);

        $payableAmount = $xml->createElement('cbc:PayableAmount', (string) $eInvoice->total_amount);
        $payableAmount->setAttribute('currencyID', $eInvoice->currency);
        $total->appendChild($payableAmount);

        $invoice->appendChild($total);
    }

    /**
     * Get invoice type code from type string.
     *
     * @param  string  $type  Invoice type
     * @return string Invoice type code
     */
    protected function getInvoiceTypeCode(string $type): string
    {
        return match ($type) {
            'e-fatura' => 'SATIS',
            'e-arsiv' => 'EARSIVFATURA',
            'e-irsaliye' => 'SEVK',
            default => 'SATIS',
        };
    }
}
