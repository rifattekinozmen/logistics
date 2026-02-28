export default {
  expo: {
    name: 'Logistics Driver',
    slug: 'logistics-driver',
    version: '1.0.0',
    orientation: 'portrait',
    userInterfaceStyle: 'automatic',
    scheme: 'logistics-driver',
    plugins: ['expo-secure-store'],
    extra: {
      apiUrl: process.env.EXPO_PUBLIC_API_URL || 'http://localhost:8000',
    },
  },
};
