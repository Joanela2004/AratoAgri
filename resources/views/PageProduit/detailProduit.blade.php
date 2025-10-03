<!Doctype html>
<html>
    <head>
        <title>Arato Agri</title>
        
</head>
        
    <body>
      <form action="{{url('/sauvegarderProduit')}}" method="POST">
        @csrf
<label>Nom :</label>
<input type="text" name="nom" placeholder="entrez le nom du produit"/>
<label>Prix/kg :</label>
<input type="number" name="prix" placeholder="entrez le prix"/>
<label>Poids :</label>
<input type="number" name="poids" placeholder="entrez le poids"/>
<label> Quantité en stock:</label>
<input type="number" name="quantiteStock" placeholder="entrez la quantité"/>
<label> image:</label>
<input type="text" name="image" placeholder="entrez l'image'"/>

<input type="submit" value="SauvegarderProduit">
</form>
</body>
</html>