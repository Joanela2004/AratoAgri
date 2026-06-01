import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../models/livraison.dart';
import '../models/livreur.dart';

class ApiService {

  static const String baseUrl = 'http://192.168.1.222:8000/api';

  Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('livreur_token');
  }

  Future<void> saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('livreur_token', token);
  }

  Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('livreur_token');
  }

  Future<Map<String, String>> _headers({bool auth = true}) async {
    final token = await getToken();
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (auth && token != null) 'Authorization': 'Bearer $token',
    };
  }

  Future<Livreur> loginLivreur(String email, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/livreur/login'),
      headers: await _headers(auth: false),
      body: jsonEncode({'email': email, 'password': password}),
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      final token = data['token'];
      await saveToken(token);

      final livreurJson = data['livreur'];
      return Livreur.fromJson(livreurJson);
    } else {
      final error = jsonDecode(response.body)['message'] ?? 'Erreur inconnue';
      throw Exception(error);
    }
  }

  Future<List<Livraison>> getMesLivraisons() async {
    final response = await http.get(
      Uri.parse('$baseUrl/livreur/mes-livraisons'),
      headers: await _headers(),
    );

    if (response.statusCode == 200) {
      final List<dynamic> jsonList = jsonDecode(response.body);
      return jsonList.map((json) => Livraison.fromJson(json)).toList();
    } else if (response.statusCode == 401) {
      await logout();
      throw Exception('Session expirée. Veuillez vous reconnecter.');
    } else {
      throw Exception('Erreur chargement livraisons : ${response.body}');
    }
  }

  Future<void> marquerLivree(int livraisonId) async {
    final response = await http.patch(
      Uri.parse('$baseUrl/livreur/livraison/$livraisonId/terminer'),
      headers: await _headers(),
    );

    if (response.statusCode != 200) {
      throw Exception('Échec : ${response.body}');
    }
  }
}