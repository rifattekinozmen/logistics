import React, { useState, useCallback } from 'react';
import { View, Text, FlatList, TouchableOpacity, StyleSheet, RefreshControl } from 'react-native';
import { api } from '../api';

export default function ShipmentsScreen({ navigation }) {
  const [list, setList] = useState([]);
  const [loading, setLoading] = useState(false);
  const [refreshing, setRefreshing] = useState(false);
  const [statusFilter, setStatusFilter] = useState('');

  const fetchShipments = useCallback(async () => {
    setLoading(true);
    try {
      const url = statusFilter ? `/api/v1/driver/shipments?status=${statusFilter}` : '/api/v1/driver/shipments';
      const res = await api(url);
      setList(res.data || []);
    } catch (e) {
      setList([]);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, [statusFilter]);

  React.useEffect(() => { fetchShipments(); }, [fetchShipments]);

  const onRefresh = () => { setRefreshing(true); fetchShipments(); };

  const statusLabels = { assigned: 'Atandı', loaded: 'Yüklendi', in_transit: 'Yolda', delivered: 'Teslim', pending: 'Bekliyor' };

  return (
    <View style={styles.container}>
      <View style={styles.filterRow}>
        {['', 'assigned', 'in_transit', 'delivered'].map((s) => (
          <TouchableOpacity key={s || 'all'} style={[styles.filterBtn, statusFilter === s && styles.filterBtnActive]} onPress={() => setStatusFilter(s)}>
            <Text style={[styles.filterText, statusFilter === s && styles.filterTextActive]}>{s ? statusLabels[s] || s : 'Tümü'}</Text>
          </TouchableOpacity>
        ))}
      </View>
      <FlatList
        data={list}
        keyExtractor={(item) => String(item.id)}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
        ListEmptyComponent={<Text style={styles.empty}>{loading ? 'Yükleniyor...' : 'Sevkiyat yok.'}</Text>}
        renderItem={({ item }) => (
          <TouchableOpacity style={styles.card} onPress={() => navigation.navigate('Detail', { shipment: item })} activeOpacity={0.7}>
            <Text style={styles.orderNo}>{item.order_number || '#' + item.id}</Text>
            <Text style={styles.addr} numberOfLines={1}>{item.pickup_address || '-'}</Text>
            <Text style={styles.addr} numberOfLines={1}>{item.delivery_address || '-'}</Text>
            <View style={styles.row}>
              <Text style={styles.status}>{statusLabels[item.status] || item.status}</Text>
              {item.vehicle_plate ? <Text style={styles.plate}>{item.vehicle_plate}</Text> : null}
            </View>
          </TouchableOpacity>
        )}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#f5f5f5' },
  filterRow: { flexDirection: 'row', padding: 12, gap: 8, backgroundColor: '#fff', borderBottomWidth: 1, borderBottomColor: '#eee' },
  filterBtn: { paddingHorizontal: 12, paddingVertical: 8, borderRadius: 8, backgroundColor: '#f0f0f0' },
  filterBtnActive: { backgroundColor: '#2563eb' },
  filterText: { fontSize: 13, color: '#333' },
  filterTextActive: { color: '#fff', fontWeight: '600' },
  card: { backgroundColor: '#fff', marginHorizontal: 16, marginVertical: 6, padding: 16, borderRadius: 12, shadowColor: '#000', shadowOpacity: 0.06, shadowRadius: 4, elevation: 2 },
  orderNo: { fontSize: 16, fontWeight: '700', color: '#111', marginBottom: 6 },
  addr: { fontSize: 13, color: '#555', marginBottom: 2 },
  row: { flexDirection: 'row', justifyContent: 'space-between', marginTop: 8 },
  status: { fontSize: 12, fontWeight: '600', color: '#2563eb' },
  plate: { fontSize: 12, color: '#666' },
  empty: { textAlign: 'center', padding: 24, color: '#666' },
});
