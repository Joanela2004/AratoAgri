class Livraison {
  final int numLivraison;
  final int numCommande;
  final String statutLivraison;
  final String? lieuLivraison;
  final String? transporteur;
  final String? referenceColis;
  final String? dateExpedition;
  final String? dateLivraison;

  Livraison({
    required this.numLivraison,
    required this.numCommande,
    required this.statutLivraison,
    this.lieuLivraison,
    this.transporteur,
    this.referenceColis,
    this.dateExpedition,
    this.dateLivraison,
  });

  factory Livraison.fromJson(Map<String, dynamic> json) {
    return Livraison(
      numLivraison: json['numLivraison'],
      numCommande: json['numCommande'],
      statutLivraison: json['statutLivraison'],
      lieuLivraison: json['lieuLivraison'],
      transporteur: json['transporteur'],
      referenceColis: json['referenceColis'],
      dateExpedition: json['dateExpedition'],
      dateLivraison: json['dateLivraison'],
    );
  }
}