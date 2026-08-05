importScripts('https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.23.0/firebase-messaging-compat.js');

// IMPORTANT: Use the same Firebase config as in the web page
firebase.initializeApp({
  apiKey: "AIzaSyDb52tGPbopgHPzyYn9f-o1SxQE7ASQLDo",
  authDomain: "invotime-9fbee.firebaseapp.com",
  projectId: "invotime-9fbee",
  storageBucket: "invotime-9fbee.firebasestorage.app",
  messagingSenderId: "727048080828",
  appId: "1:727048080828:web:52b0edaa10d0853edc2fb9",
  measurementId: "G-VTGF1V8PN8",
});

const messaging = firebase.messaging();

// Optional: Handle background messages
messaging.onBackgroundMessage((payload) => {
    const title = payload?.notification?.title || 'Notification';
    const options = {
        body: payload?.notification?.body || '',
        icon: '/assets/favicon.ico'
    };
    self.registration.showNotification(title, options);
});
