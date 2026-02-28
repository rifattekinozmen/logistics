import React from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { View, TouchableOpacity, StyleSheet, ActivityIndicator, Text } from 'react-native';
import { StatusBar } from 'expo-status-bar';
import { AuthProvider, useAuth } from './src/auth-context';
import LoginScreen from './src/screens/LoginScreen';
import ShipmentsScreen from './src/screens/ShipmentsScreen';
import ShipmentDetailScreen from './src/screens/ShipmentDetailScreen';

const Stack = createNativeStackNavigator();

function MainStack() {
  return (
    <Stack.Navigator screenOptions={{ headerShadowVisible: false }}>
      <Stack.Screen
        name="Shipments"
        component={ShipmentsScreen}
        options={{
          title: 'Sevkiyatlar',
          headerRight: () => <LogoutButton />,
        }}
      />
      <Stack.Screen name="Detail" component={ShipmentDetailScreen} options={{ title: 'Sevkiyat detay' }} />
    </Stack.Navigator>
  );
}

function LogoutButton() {
  const { logout } = useAuth();
  return (
    <TouchableOpacity onPress={logout} style={styles.logoutBtn}>
      <Text style={styles.logoutText}>Çıkış</Text>
    </TouchableOpacity>
  );
}

function AppNavigator() {
  const { user, loading } = useAuth();

  if (loading) {
    return (
      <View style={styles.centered}>
        <ActivityIndicator size="large" color="#2563eb" />
      </View>
    );
  }

  if (!user) {
    return (
      <>
        <StatusBar style="dark" />
        <LoginScreen onSuccess={() => {}} />
      </>
    );
  }

  return (
    <>
      <StatusBar style="dark" />
      <NavigationContainer>
        <Stack.Navigator screenOptions={{ headerShown: false }}>
          <Stack.Screen name="Main" component={MainStack} />
        </Stack.Navigator>
      </NavigationContainer>
    </>
  );
}

export default function App() {
  return (
    <AuthProvider>
      <AppNavigator />
    </AuthProvider>
  );
}

const styles = StyleSheet.create({
  centered: { flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: '#f5f5f5' },
  logoutBtn: { paddingHorizontal: 12, paddingVertical: 6 },
  logoutText: { color: '#2563eb', fontWeight: '600' },
});
