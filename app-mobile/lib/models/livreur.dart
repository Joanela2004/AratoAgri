class Livreur {
  final int numLivreur;
  final String nom;
  final String email;
  final String contact;

  Livreur({
    required this.numLivreur,
    required this.nom,
    required this.email,
    required this.contact
  });

  factory Livreur.fromJson(Map<String, dynamic> json) {
    return Livreur(
      // 1. Correction de la clé pour l'ID (Laravel renvoie souvent 'id' dans le JSON d'auth)
      // On utilise 'as int' en s'assurant qu'il y a une valeur par défaut (0) si c'est null
      numLivreur: (json['id'] ?? json['numLivreur'] ?? 0) as int,
      
      // 2. Sécurisation des chaînes de caractères avec l'opérateur '??'
      nom: json['nom'] ?? '',
      email: json['email'] ?? '',
      
      // 3. Correction de la clé pour le téléphone ('telephone' côté Laravel, 'contact' côté Flutter)
      contact: json['telephone'] ?? json['contact'] ?? '',
      
       );
  }
}