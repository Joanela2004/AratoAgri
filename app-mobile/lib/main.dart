import 'package:flutter/material.dart';
import 'login.dart';
import 'register.dart';
import 'liste.dart'; 

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'Arato Agri',
      initialRoute: '/login',
      routes: {
        '/login': (context) => const LoginPage(),
        '/register': (context) => const RegisterPage(),
        '/liste': (context) => const ListePage(), // page après connexion
      },
    );
  }
}
