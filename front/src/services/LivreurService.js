// src/services/LivreurService.js

import api from "./api";           // ← AJOUTER CETTE LIGNE (même chemin que dans livraisonService)

const LIVREUR_URL = "/livreurs";

const getConfig = (isFormData = false) => {
  const token = localStorage.getItem("userToken");
  if (!token) throw new Error("Utilisateur non authentifié");
  const headers = { Authorization: `Bearer ${token}` };
  if (isFormData) headers["Content-Type"] = "multipart/form-data";
  return { headers, withCredentials: true };
};

export const fetchLivreurs = async () => {
  const res = await api.get(LIVREUR_URL, getConfig());
  return res.data.data; // ← On ajoute ".data" pour cibler le tableau de livreurs
};

export const createLivreur = async (data) => {
  console.log(data)
  const res = await api.post(LIVREUR_URL, data, getConfig());
  return res.data;
};

export const updateLivreur = async (id, data) => {
  const res = await api.put(`${LIVREUR_URL}/${id}`, data, getConfig());
  return res.data;
};

export const deleteLivreur = async (id) => {
  const res = await api.delete(`${LIVREUR_URL}/${id}`, getConfig());
  return res.data;
};

// ====================== ACTIONS DU LIVREUR CONNECTÉ ======================
export const fetchMesLivraisons = async () => {
  const res = await api.get("/livreur/mes-livraisons", getConfig());
  return res.data;
};

export const marquerLivraisonTerminee = async (idLivraison) => {
  const res = await api.patch(`/livreur/livraison/${idLivraison}/terminer`, {}, getConfig());
  return res.data;
};