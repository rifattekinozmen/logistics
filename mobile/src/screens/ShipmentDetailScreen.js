import React, { useState } from 'react';
import { View, Text, ScrollView, TouchableOpacity, StyleSheet, Alert } from 'react-native';
import * as ImagePicker from 'expo-image-picker';
import { api, apiMultipart } from '../api';

const statusOrder = ['assigned', 'loaded', 'in_transit', 'delivered'];
const statusLabels = { assigned: 'Atandı', loaded: 'Yüklendi', in_transit: 'Yolda', delivered: 'Teslim edildi' };

export default function ShipmentDetailScreen({ route, navigation }) {
  const shipment = route.params?.shipment || {};
  const [status, setStatus] = useState(shipment?.status || '');
  const onBack = () => navigation.goBack();
  const onUpdated = () => {};
  const [loading, setLoading] = useState(false);

  const nextStatus = () => {
    const i = statusOrder.indexOf(status);
    if (i < 0 || i >= statusOrder.length - 1) return null;
    return statusOrder[i + 1];
  };

  const updateStatus = async () => {
    const next = nextStatus();
    if (!next) return;
    setLoading(true);
    try {
      await api(`/api/v1/driver/shipments/${shipment.id}/status`, {
        method: 'PUT',
        body: JSON.stringify({ status: next }),
      });
      setStatus(next);
      onUpdated?.();
      Alert.alert('Başarılı', 'Durum güncellendi.');
    } catch (e) {
      Alert.alert('Hata', e.message || 'Güncellenemedi.');
    } finally {
      setLoading(false);
    }
  };

  const sendLocation = async () => {
    setLoading(true);
    try {
      await api('/api/v1/driver/location', {
        method: 'POST',
        body: JSON.stringify({
          latitude: 41.0082,
          longitude: 28.9784,
          shipment_id: shipment.id,
        }),
      });
      Alert.alert('Başarılı', 'Konum gönderildi.');
    } catch (e) {
      Alert.alert('Hata', e.message || 'Gönderilemedi.');
    } finally {
      setLoading(false);
    }
  };

  const uploadPod = async () => {
    const { status: perm } = await ImagePicker.requestMediaLibraryPermissionsAsync();
    if (perm !== 'granted') {
      Alert.alert('İzin gerekli', 'Galeri erişimi gerekli.');
      return;
    }
    const result = await ImagePicker.launchImageLibraryAsync({ mediaTypes: ImagePicker.MediaTypeOptions.Images, allowsEditing: true, quality: 0.8 });
    if (result.canceled) return;
    const uri = result.assets[0].uri;
    const formData = new FormData();
    formData.append('pod_file', { uri, name: 'pod.jpg', type: 'image/jpeg' });
    setLoading(true);
    try {
      await apiMultipart(`/api/v1/driver/shipments/${shipment.id}/pod`, formData);
      Alert.alert('Başarılı', 'POD yüklendi.');
    } catch (e) {
      Alert.alert('Hata', e.message || 'Yüklenemedi.');
    } finally {
      setLoading(false);
    }
  };

  const next = nextStatus();

  return (
    <ScrollView style={styles.container}>
      <View style={styles.card}>
        <Text style={styles.orderNo}>{shipment.order_number || '#' + shipment.id}</Text>
        <Text style={styles.label}>Alış</Text>
        <Text style={styles.value}>{shipment.pickup_address || '-'}</Text>
        <Text style={styles.label}>Teslim</Text>
        <Text style={styles.value}>{shipment.delivery_address || '-'}</Text>
        <Text style={styles.status}>Durum: {statusLabels[status] || status}</Text>
      </View>
      {next && (
        <TouchableOpacity style={[styles.btn, loading && styles.btnDisabled]} onPress={updateStatus} disabled={loading}>
          <Text style={styles.btnText}>Durumu "{statusLabels[next]}" yap</Text>
        </TouchableOpacity>
      )}
      <TouchableOpacity style={[styles.btnSecondary, loading && styles.btnDisabled]} onPress={sendLocation} disabled={loading}>
        <Text style={styles.btnTextSecondary}>Konum gönder</Text>
      </TouchableOpacity>
      <TouchableOpacity style={[styles.btnSecondary, loading && styles.btnDisabled]} onPress={uploadPod} disabled={loading}>
        <Text style={styles.btnTextSecondary}>POD yükle (galeri)</Text>
      </TouchableOpacity>
      <TouchableOpacity style={styles.backBtn} onPress={onBack}>
        <Text style={styles.btnTextSecondary}>Listeye dön</Text>
      </TouchableOpacity>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#f5f5f5' },
  card: { backgroundColor: '#fff', margin: 16, padding: 16, borderRadius: 12, shadowColor: '#000', shadowOpacity: 0.06, shadowRadius: 4, elevation: 2 },
  orderNo: { fontSize: 18, fontWeight: '700', color: '#111', marginBottom: 12 },
  label: { fontSize: 11, color: '#888', marginTop: 8 },
  value: { fontSize: 14, color: '#333' },
  status: { marginTop: 12, fontSize: 14, fontWeight: '600', color: '#2563eb' },
  btn: { backgroundColor: '#2563eb', marginHorizontal: 16, marginTop: 8, padding: 14, borderRadius: 8, alignItems: 'center' },
  btnSecondary: { marginHorizontal: 16, marginTop: 8, padding: 14, borderRadius: 8, alignItems: 'center', borderWidth: 1, borderColor: '#2563eb' },
  backBtn: { marginHorizontal: 16, marginTop: 16, marginBottom: 24, padding: 14, alignItems: 'center' },
  btnDisabled: { opacity: 0.6 },
  btnText: { color: '#fff', fontWeight: '600' },
  btnTextSecondary: { color: '#2563eb', fontWeight: '600' },
});
