import 'package:flutter/material.dart';
import 'SearchPage.dart';

class ListePage extends StatefulWidget {
  const ListePage({super.key});

  @override
  State<ListePage> createState() => _ListePageState();
}

class _ListePageState extends State<ListePage> {
  Color myGreen = const Color(0xFF28A458);

  // 1. DÉCLARATION DES CONTRÔLEURS (Indispensable pour le formulaire)
  final TextEditingController _nameController = TextEditingController(text: "Jean Louis");
  final TextEditingController _phoneController = TextEditingController(text: "034 00 000 00");

  // Données de la commande
  Map<String, dynamic> order = {
    "id": "CMD-120012",
    "name": "Jean Louis",
    "address": "Sahalava, Fianarantsoa",
    "price": "140000 Ar",
    "time": "14:30",
    "status": "En cours",
  };

  // Libérer la mémoire quand on quitte la page
  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    super.dispose();
  }

  void _showProfileForm() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text("Modifier le Profil"),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: _nameController,
              decoration: const InputDecoration(
                labelText: "Nom complet",
                prefixIcon: Icon(Icons.person),
              ),
            ),
            const SizedBox(height: 15),
            TextField(
              controller: _phoneController,
              keyboardType: TextInputType.phone,
              decoration: const InputDecoration(
                labelText: "Téléphone",
                prefixIcon: Icon(Icons.phone),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text("Annuler", style: TextStyle(color: Colors.grey)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: myGreen),
            onPressed: () {
              setState(() {
                // Ici on simule la mise à jour
              });
              Navigator.pop(context);
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text("Profil mis à jour !")),
              );
            },
            child: const Text("Sauvegarder", style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  void _showUpdateBottomSheet(Map<String, dynamic> orderData) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => Container(
        padding: const EdgeInsets.all(16),
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Center(
              child: Container(
                width: 50,
                height: 5,
                decoration: BoxDecoration(
                  color: Colors.grey[300],
                  borderRadius: BorderRadius.circular(10),
                ),
              ),
            ),
            const SizedBox(height: 16),
            Text(
              "Mise à jour de la commande",
              style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: Colors.grey[800]),
            ),
            const SizedBox(height: 16),
            ListTile(
              contentPadding: EdgeInsets.zero,
              leading: Container(
                decoration: BoxDecoration(
                  color: Colors.grey[200],
                  shape: BoxShape.circle,
                ),
                padding: const EdgeInsets.all(8),
                child: const Icon(Icons.lock_open_rounded,
                    size: 22, color: Colors.grey),
              ),
              title: Text(orderData['id'],
                  style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                      color: Colors.black87)),
              subtitle: Text("${orderData['name']}\n${orderData['address']}",
                  style: const TextStyle(fontSize: 12, color: Colors.grey)),
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: TextFormField(
                    initialValue: orderData['price'],
                    enabled: false,
                    decoration: InputDecoration(
                      labelText: "Prix",
                      border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(8)),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: TextFormField(
                    initialValue: orderData['time'],
                    enabled: false,
                    decoration: InputDecoration(
                      labelText: "Heure",
                      border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(8)),
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            ElevatedButton(
              style: ElevatedButton.styleFrom(
                backgroundColor: myGreen,
                minimumSize: const Size(double.infinity, 50),
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12)),
              ),
              child: const Text("Marquer comme Livrée",
                  style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: Colors.white)),
              onPressed: () {
                setState(() {
                  order['status'] = "Livrée";
                });
                Navigator.pop(context);
                ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                  content: Text("Commande ${order['id']} marquée comme Livrée"),
                  duration: const Duration(seconds: 2),
                ));
              },
            ),
            const SizedBox(height: 20),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    // Remplacement de WillPopScope par PopScope
    return PopScope(
      canPop: false, // Bloque le retour arrière
      child: Scaffold(
        appBar: AppBar(
          automaticallyImplyLeading: false,
          title: Row(
            children: [
              Image.asset('assets/log.png', height: 40, errorBuilder: (c, e, s) => const Icon(Icons.eco)), // Fallback si image absente
              const SizedBox(width: 10),
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text.rich(
                    TextSpan(children: [
                      const TextSpan(
                          text: "Arato ",
                          style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                              color: Colors.black87)),
                      TextSpan(
                          text: "Agri",
                          style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                              color: myGreen)),
                    ]),
                  ),
                  const Text("PLATEFORME LIVREUR",
                      style: TextStyle(fontSize: 10, color: Colors.grey)),
                ],
              ),
            ],
          ),
          actions: [
            IconButton(
              icon: const Icon(Icons.search),
              onPressed: () => Navigator.push(context,
                  MaterialPageRoute(builder: (context) => const SearchPage())),
            ),
            PopupMenuButton<String>(
              onSelected: (value) {
                if (value == 'profil') _showProfileForm();
                if (value == 'logout') Navigator.pushReplacementNamed(context, '/login');
              },
              itemBuilder: (context) => [
                const PopupMenuItem(
                    value: 'profil',
                    child: Row(children: [Icon(Icons.person), Text(" Profil")])),
                const PopupMenuItem(
                    value: 'logout',
                    child: Row(children: [Icon(Icons.logout, color: Colors.red), Text(" Déconnexion", style: TextStyle(color: Colors.red))])),
              ],
            ),
          ],
        ),
        body: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text("Mission & Statut",
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
              const SizedBox(height: 16),
              _buildMissionCard(),
              const SizedBox(height: 16),
              Row(
                children: [
                  _buildStatCard("Livrées", "2"),
                  _buildStatCard("En cours", "2"),
                ],
              ),
              const SizedBox(height: 20),
              Text("LISTE DES COMMANDES",
                  style: TextStyle(
                      color: Colors.grey[400],
                      fontWeight: FontWeight.bold,
                      letterSpacing: 1.2)),
              const SizedBox(height: 10),
              _buildOrderTile(),
            ],
          ),
        ),
      ),
    );
  }

  // --- Widgets de construction pour plus de clarté ---

  Widget _buildMissionCard() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
          color: myGreen, borderRadius: BorderRadius.circular(20)),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          const Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text("TOTAL MISSIONS",
                  style: TextStyle(color: Colors.white70, fontSize: 12)),
              Text("4",
                  style: TextStyle(
                      color: Colors.white,
                      fontSize: 24,
                      fontWeight: FontWeight.bold)),
            ],
          ),
          CircleAvatar(
            backgroundColor: Colors.white24,
            child: IconButton(
                icon: const Icon(Icons.shopping_bag, color: Colors.white),
                onPressed: () {}),
          )
        ],
      ),
    );
  }

  Widget _buildStatCard(String title, String value) {
    return Expanded(
      child: Container(
        margin: const EdgeInsets.symmetric(horizontal: 4),
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          boxShadow: [
            BoxShadow(
                color: Colors.black.withOpacity(0.05),
                blurRadius: 10,
                offset: const Offset(0, 4))
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                CircleAvatar(radius: 3, backgroundColor: myGreen),
                const SizedBox(width: 6),
                Text(title.toUpperCase(),
                    style: const TextStyle(fontSize: 11, color: Colors.grey)),
              ],
            ),
            const SizedBox(height: 8),
            Text(value,
                style:
                    const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
          ],
        ),
      ),
    );
  }

  Widget _buildOrderTile() {
    return Card(
      elevation: 0,
      shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
          side: BorderSide(color: Colors.grey.shade100)),
      child: ListTile(
        onTap: () => _showUpdateBottomSheet(order),
        leading: CircleAvatar(
          backgroundColor: Colors.grey[100],
          child: const Icon(Icons.local_shipping, color: Colors.grey),
        ),
        title: Row(
          children: [
            Text(order['id'],
                style: const TextStyle(fontSize: 12, color: Colors.grey)),
            const SizedBox(width: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
              decoration: BoxDecoration(
                  color: order['status'] == "Livrée" 
                      ? myGreen.withOpacity(0.1) 
                      : Colors.orange.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(4)),
              child: Text(order['status'],
                  style: TextStyle(
                      fontSize: 10,
                      color: order['status'] == "Livrée" ? myGreen : Colors.orange,
                      fontWeight: FontWeight.bold)),
            )
          ],
        ),
        subtitle: Text(order['name'],
            style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.black)),
        trailing: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          crossAxisAlignment: CrossAxisAlignment.end,
          children: [
            Text(order['price'],
                style: const TextStyle(fontWeight: FontWeight.bold)),
            Text(order['time'],
                style: const TextStyle(fontSize: 11, color: Colors.grey)),
          ],
        ),
      ),
    );
  }
}