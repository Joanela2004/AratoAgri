import React from "react";
import { FaWeightHanging, FaMapMarkerAlt } from "react-icons/fa";

export const FraisOnglet = () => (
  <div style={{ padding: "20px", backgroundColor: "#fff", borderRadius: "8px" }}>
    <h2><FaWeightHanging color="#28a458" /> Gestion des Frais de Livraison</h2>
    <p style={{ color: "#666" }}>Configuration des tarifs par poids, distance ou zones.</p>
  </div>
);

export const LieuxOnglet = () => (
  <div style={{ padding: "20px", backgroundColor: "#fff", borderRadius: "8px" }}>
    <h2><FaMapMarkerAlt color="#dc3545" /> Gestion des Lieux de Livraison</h2>
    <p style={{ color: "#666" }}>Ajustement des points de collecte et zones admissibles.</p>
  </div>
);