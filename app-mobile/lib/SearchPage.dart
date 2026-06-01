import 'package:flutter/material.dart';

class SearchPage extends StatefulWidget {
  const SearchPage({super.key});

  @override
  State<SearchPage> createState() => _SearchPageState();
}

class _SearchPageState extends State<SearchPage> {
  final TextEditingController _controller = TextEditingController();
  bool _hasText = false;
  Color myGreen = const Color(0xFF28A458);

  // Données de test
  final List<Map<String, String>> allResults = [
    {"id": "CMD-120012", "name": "Jean Louis", "address": "Sahalava, Fianarantsoa", "price": "140000 Ar", "time": "14:30"},
    {"id": "CMD-201850", "name": "Sitraka Henintsoa", "address": "Sahalava, Fianarantsoa", "price": "8000 Ar", "time": "08:10"},
    {"id": "CMD-301900", "name": "Ranaivo Andry", "address": "Ambalavao, Fianarantsoa", "price": "12000 Ar", "time": "09:45"},
  ];

  List<Map<String, String>> filteredResults = [];

  @override
  void initState() {
    super.initState();
    filteredResults = allResults;
    _controller.addListener(() {
      setState(() {
        _hasText = _controller.text.isNotEmpty;
        _filterResults(_controller.text);
      });
    });
  }

  void _filterResults(String query) {
    setState(() {
      filteredResults = allResults
          .where((item) =>
              item["id"]!.toLowerCase().contains(query.toLowerCase()) ||
              item["name"]!.toLowerCase().contains(query.toLowerCase()) ||
              item["address"]!.toLowerCase().contains(query.toLowerCase()))
          .toList();
    });
  }

  // --- FONCTION POUR LE FORMULAIRE DE PROFIL ---
  void _showProfileForm() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(25)),
      ),
      builder: (context) => Padding(
        padding: EdgeInsets.only(
            bottom: MediaQuery.of(context).viewInsets.bottom, 
            left: 20, right: 20, top: 20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(width: 50, height: 5, decoration: BoxDecoration(color: Colors.grey[300], borderRadius: BorderRadius.circular(10))),
            const SizedBox(height: 20),
            Text("Modifier le Profil", style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: myGreen)),
            const SizedBox(height: 20),
            _buildProfileField("Nom Complet", "Jean Louis"),
            const SizedBox(height: 15),
            _buildProfileField("Téléphone", "+261 34 00 000 00"),
            const SizedBox(height: 25),
            ElevatedButton(
              onPressed: () => Navigator.pop(context),
              style: ElevatedButton.styleFrom(
                backgroundColor: myGreen,
                minimumSize: const Size(double.infinity, 50),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              child: const Text("Enregistrer les modifications", style: TextStyle(color: Colors.white)),
            ),
            const SizedBox(height: 20),
          ],
        ),
      ),
    );
  }

  Widget _buildProfileField(String label, String initialValue) {
    return TextFormField(
      initialValue: initialValue,
      decoration: InputDecoration(
        labelText: label,
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
        focusedBorder: OutlineInputBorder(borderSide: BorderSide(color: myGreen)),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        automaticallyImplyLeading: false,
        backgroundColor: Colors.white,
        elevation: 0,
        title: Row(
          children: [
            // BARRE DE RECHERCHE
            Expanded(
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 5),
                decoration: BoxDecoration(
                  color: Colors.grey[200],
                  borderRadius: BorderRadius.circular(17),
                ),
                child: TextField(
                  controller: _controller,
                  decoration: InputDecoration(
                    hintText: "Rechercher...",
                    hintStyle: const TextStyle(color: Colors.grey, fontSize: 14),
                    prefixIcon: IconButton(
                      icon: const Icon(Icons.arrow_back_ios, size: 18),
                      onPressed: () => Navigator.pop(context),
                    ),
                    suffixIcon: Icon(_hasText ? Icons.close : Icons.search, size: 18),
                    border: InputBorder.none,
                  ),
                ),
              ),
            ),
            
            // --- MENU DE DROITE ---
            PopupMenuButton<String>(
              icon: const Icon(Icons.more_vert, color: Colors.grey),
              onSelected: (value) {
                if (value == 'profil') {
                  _showProfileForm();
                } else if (value == 'logout') {
                  Navigator.pushReplacementNamed(context, '/login');
                }
              },
              itemBuilder: (BuildContext context) => [
                const PopupMenuItem(
                  value: 'profil',
                  child: ListTile(
                    leading: Icon(Icons.person_outline),
                    title: Text('Profil'),
                    contentPadding: EdgeInsets.zero,
                  ),
                ),
                const PopupMenuItem(
                  value: 'logout',
                  child: ListTile(
                    leading: Icon(Icons.logout, color: Colors.red),
                    title: Text('Se déconnecter', style: TextStyle(color: Colors.red)),
                    contentPadding: EdgeInsets.zero,
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
      body: filteredResults.isEmpty 
        ? const Center(child: Text("Aucun résultat"))
        : ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: filteredResults.length,
            itemBuilder: (context, index) {
              final item = filteredResults[index];
              return _buildCard(item);
            },
          ),
    );
  }

  Widget _buildCard(Map<String, String> item) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(10),
        boxShadow: [BoxShadow(color: Colors.grey.withOpacity(0.1), blurRadius: 6)],
      ),
      child: ListTile(
        contentPadding: EdgeInsets.zero,
        leading: CircleAvatar(backgroundColor: Colors.grey[200], child: const Icon(Icons.lock_open)),
        title: Text(item["name"]!, style: const TextStyle(fontWeight: FontWeight.bold)),
        subtitle: Text(item["address"]!),
        trailing: Text(item["price"]!, style: TextStyle(color: myGreen, fontWeight: FontWeight.bold)),
      ),
    );
  }
}