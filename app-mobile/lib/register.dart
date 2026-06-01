import 'package:flutter/material.dart';
import '../services/api_service.dart'; // Assure-toi que ce chemin est correct
import 'dart:convert';
import 'package:http/http.dart' as http;

class RegisterPage extends StatefulWidget {
  const RegisterPage({super.key});

  @override
  State<RegisterPage> createState() => _RegisterPageState();
}

class _RegisterPageState extends State<RegisterPage> {
  final Color myGreen = const Color(0xFF28A458);

  // Contrôleurs pour tous les champs
  final _nomController = TextEditingController();
  final _phoneController = TextEditingController();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();

  bool _isLoading = false;
  String? _errorMessage;

  @override
  void dispose() {
    _nomController.dispose();
    _phoneController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _register() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    // Validation simple côté front
    if (_nomController.text.trim().isEmpty ||
        _phoneController.text.trim().isEmpty ||
        _emailController.text.trim().isEmpty ||
        _passwordController.text.trim().isEmpty) {
      setState(() {
        _errorMessage = "Tous les champs sont obligatoires";
        _isLoading = false;
      });
      return;
    }

    if (_passwordController.text.length < 6) {
      setState(() {
        _errorMessage = "Le mot de passe doit contenir au moins 6 caractères";
        _isLoading = false;
      });
      return;
    }

    try {
      final api = ApiService();

      // Prépare les données pour Laravel
      final response = await http.post(
        Uri.parse('${ApiService.baseUrl}/livreurs'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'nom': _nomController.text.trim(),
          'email': _emailController.text.trim(),
          'password': _passwordController.text.trim(),
          'contact': _phoneController.text.trim(),
          'matricule_vehicule': '', // optionnel, tu peux ajouter un champ si besoin
        }),
      );

      if (response.statusCode == 201) {
        final data = jsonDecode(response.body);
        // Succès → on peut connecter directement ou rediriger vers login
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text("Inscription réussie ! Vous pouvez maintenant vous connecter."),
            backgroundColor: Colors.green,
          ),
        );
        Navigator.pop(context); // Retour à login
      } else {
        final errorData = jsonDecode(response.body);
        String msg = "Erreur lors de l'inscription";

        if (response.statusCode == 422) {
          // Erreurs de validation Laravel
          if (errorData['errors']?['email'] != null) {
            msg = "Cet email est déjà utilisé";
          } else {
            msg = errorData['message'] ?? msg;
          }
        } else {
          msg = errorData['message'] ?? msg;
        }

        setState(() {
          _errorMessage = msg;
        });
      }
    } catch (e) {
      setState(() {
        _errorMessage = "Erreur réseau : $e";
      });
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 30),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            const SizedBox(height: 40),
            Center(child: Image.asset('assets/log.png', height: 80)),
            const SizedBox(height: 20),
            Text.rich(
              TextSpan(
                children: [
                  TextSpan(
                    text: "Arato ",
                    style: TextStyle(fontSize: 28, fontWeight: FontWeight.bold, color: Colors.grey[800]),
                  ),
                  TextSpan(
                    text: "Agri",
                    style: TextStyle(fontSize: 28, fontWeight: FontWeight.bold, color: myGreen),
                  ),
                ],
              ),
              textAlign: TextAlign.center,
            ),
            const Text(
              "PLATEFORME LIVREUR",
              style: TextStyle(letterSpacing: 2, color: Colors.grey, fontSize: 12),
            ),
            const SizedBox(height: 40),

            // Message d'erreur
            if (_errorMessage != null)
              Padding(
                padding: const EdgeInsets.only(bottom: 20),
                child: Text(
                  _errorMessage!,
                  style: const TextStyle(color: Colors.red, fontSize: 14),
                  textAlign: TextAlign.center,
                ),
              ),

            // Champs de saisie
            _buildInput(Icons.person_outline, "Nom complet", myGreen, controller: _nomController),
            const SizedBox(height: 15),
            _buildInput(Icons.phone_android_outlined, "Numéro de téléphone", myGreen, controller: _phoneController),
            const SizedBox(height: 15),
            _buildInput(Icons.email_outlined, "Email", myGreen, controller: _emailController),
            const SizedBox(height: 15),
            _buildInput(Icons.lock_outline, "Mot de passe", myGreen, isPass: true, controller: _passwordController),
            const SizedBox(height: 40),

            // Bouton d'inscription
            ElevatedButton(
              onPressed: _isLoading ? null : _register,
              style: ElevatedButton.styleFrom(
                backgroundColor: myGreen,
                minimumSize: const Size(double.infinity, 55),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15)),
              ),
              child: _isLoading
                  ? const SizedBox(
                      height: 24,
                      width: 24,
                      child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2.5),
                    )
                  : const Text(
                      "S'inscrire",
                      style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Colors.white),
                    ),
            ),
            const SizedBox(height: 20),

            // Lien vers la connexion
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Text("Déjà un compte ?", style: TextStyle(color: Colors.grey)),
                TextButton(
                  onPressed: () => Navigator.pop(context),
                  child: Text("Se connecter", style: TextStyle(color: myGreen, fontWeight: FontWeight.bold)),
                ),
              ],
            ),
            const SizedBox(height: 30),
          ],
        ),
      ),
    );
  }

  Widget _buildInput(IconData icon, String hint, Color color,
      {bool isPass = false, required TextEditingController controller}) {
    return TextField(
      controller: controller,
      obscureText: isPass,
      decoration: InputDecoration(
        prefixIcon: Icon(icon, color: color),
        hintText: hint,
        filled: true,
        fillColor: Colors.grey[50],
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(15),
          borderSide: BorderSide(color: Colors.grey[200]!),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(15),
          borderSide: BorderSide(color: color),
        ),
      ),
    );
  }
}