import React, { useState, useEffect } from "react";
import {
  FaSearch,
  FaSync,
  FaUserTie,
  FaEnvelope,
  FaPhone,
  FaCar,
  FaInfoCircle,
  FaPlus,
  FaTimes,
  FaEdit,
  FaTrashAlt,
} from "react-icons/fa";
// Ajout des fonctions de mise à jour et suppression dans les imports
import { fetchLivreurs, createLivreur, updateLivreur, deleteLivreur } from "../../../services/LivreurService";
import "../../../styles/back-office/global.css";
import "../../../styles/back-office/tableau.css";
import "../../../styles/back-office/modal.css";

const LivreursOnglet = () => {
  const [livreurs, setLivreurs] = useState([]);
  const [searchTerm, setSearchTerm] = useState("");
  const [loading, setLoading] = useState(true);
  const [selectedLivreur, setSelectedLivreur] = useState(null);
  
  // États pour les modals
  const [isAddModalOpen, setIsAddModalOpen] = useState(false);
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [isDeleteModalOpen, setIsDeleteModalOpen] = useState(false);
  const [livreurToAction, setLivreurToAction] = useState(null); // Stocke le livreur ciblé pour edit/delete
  
  // Formulaires
  const [formData, setFormData] = useState({
    nom: "",
    email: "",
    password: "",
    telephone: ""
  });
  const [editFormData, setEditFormData] = useState({
    nom: "",
    email: "",
    telephone: "",
    statut: "disponible"
  });

  const [submitting, setSubmitting] = useState(false);
  const [errorMessage, setErrorMessage] = useState("");

  const loadData = async () => {
    try {
      setLoading(true);
      const res = await fetchLivreurs();
      // Gestion de la structure selon ton choix d'extraction (Axios vs Service)
      const data = res.data ? res.data : res;
      setLivreurs(data);
    } catch (err) {
      console.error("Erreur de chargement des livreurs:", err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, []);

  const filteredLivreurs = livreurs.filter(
    (l) =>
      l.nom?.toLowerCase().includes(searchTerm.toLowerCase()) ||
      l.email?.toLowerCase().includes(searchTerm.toLowerCase()) ||
      l.telephone?.toLowerCase().includes(searchTerm.toLowerCase())
  );

  // Handlers pour les inputs
  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleEditInputChange = (e) => {
    const { name, value } = e.target;
    setEditFormData((prev) => ({ ...prev, [name]: value }));
  };

  // --- ACTIONS : CREATE ---
  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    setErrorMessage("");

    try {
      await createLivreur(formData);
      setIsAddModalOpen(false);
      setFormData({ nom: "", email: "", password: "", telephone: "" });
      await loadData();
      alert("Livreur ajouté avec succès !");
    } catch (err) {
      console.error("Erreur création livreur:", err);
      setErrorMessage(err.response?.data?.message || "Erreur lors de l'ajout.");
    } finally {
      setSubmitting(false);
    }
  };

  // --- ACTIONS : OUVRE MODAL EDIT ---
  const openEditModal = (e, livreur) => {
    e.stopPropagation(); // Évite de déclencher la sélection de la ligne du tableau
    setLivreurToAction(livreur);
    setEditFormData({
      nom: livreur.nom || "",
      email: livreur.email || "",
      telephone: livreur.telephone || "",
      statut: livreur.statut || "disponible"
    });
    setErrorMessage("");
    setIsEditModalOpen(true);
  };

  // --- ACTIONS : UPDATE ---
  const handleEditSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    setErrorMessage("");

    try {
      const id = livreurToAction.id || livreurToAction.numLivreur;
      await updateLivreur(id, editFormData);
      setIsEditModalOpen(false);
      
      // Si le livreur modifié était actuellement affiché en détail, on rafraîchit la carte focus
      if (selectedLivreur && (selectedLivreur.id === id || selectedLivreur.numLivreur === id)) {
        setSelectedLivreur({ ...selectedLivreur, ...editFormData });
      }
      
      await loadData();
      alert("Livreur mis à jour avec succès !");
    } catch (err) {
      console.error("Erreur modification livreur:", err);
      setErrorMessage(err.response?.data?.message || "Erreur lors de la modification.");
    } finally {
      setSubmitting(false);
    }
  };

  // --- ACTIONS : OUVRE MODAL DELETE ---
  const openDeleteModal = (e, livreur) => {
    e.stopPropagation(); // Évite de sélectionner la ligne
    setLivreurToAction(livreur);
    setIsDeleteModalOpen(true);
  };

  // --- ACTIONS : DELETE ---
  const handleDeleteConfirm = async () => {
    setSubmitting(true);
    try {
      const id = livreurToAction.id || livreurToAction.numLivreur;
      await deleteLivreur(id);
      setIsDeleteModalOpen(false);
      
      // Ferme la carte focus si on supprime ce livreur
      if (selectedLivreur && (selectedLivreur.id === id || selectedLivreur.numLivreur === id)) {
        setSelectedLivreur(null);
      }
      
      await loadData();
      alert("Livreur supprimé avec succès !");
    } catch (err) {
      console.error("Erreur suppression livreur:", err);
      alert("Impossible de supprimer ce livreur.");
    } finally {
      setSubmitting(false);
    }
  };

  // Badge couleur dynamique pour le statut
  const getStatusStyle = (statut) => {
    switch (statut) {
      case "disponible":
        return { backgroundColor: "#c6f6d5", color: "#22543d" };
      case "en_livraison":
        return { backgroundColor: "#feebc8", color: "#744210" };
      case "indisponible":
        return { backgroundColor: "#fed7d7", color: "#742a2a" };
      default:
        return { backgroundColor: "#e2e8f0", color: "#4a5568" };
    }
  };

  return (
    <div className="tab-pane-container" style={{ animation: "fadeIn 0.2s ease" }}>
      
      {/* Focus Carte Livreur Sélectionné */}
      {selectedLivreur && (
        <div className="selected-card" style={{ backgroundColor: "#f8f9fa", borderLeft: "4px solid #28a458", padding: "16px 20px", borderRadius: "6px", marginBottom: "20px", boxShadow: "0 1px 3px rgba(0,0,0,0.05)", position: "relative" }}>
          <button onClick={() => setSelectedLivreur(null)} style={{ position: "absolute", top: "12px", right: "12px", background: "none", border: "none", cursor: "pointer", color: "#a0aec0" }}>
            <FaTimes size={16} />
          </button>
          <h3 style={{ margin: "0 0 12px 0", color: "#2d3748", display: "flex", alignItems: "center", gap: "8px", fontSize: "1.1rem" }}>
            <FaUserTie color="#28a458"/> Détails de {selectedLivreur.nom}
          </h3>
          <div style={{ display: "flex", gap: "40px", flexWrap: "wrap", fontSize: "0.9rem", color: "#4a5568" }}>
            <p style={{ margin: "4px 0" }}><strong><FaCar /> Véhicule Matricule :</strong> <code style={{ backgroundColor: "#e2e8f0", padding: "2px 6px", borderRadius: "4px" }}>{selectedLivreur.matricule_vehicule || "Non renseigné"}</code></p>
            <p style={{ margin: "4px 0" }}><strong><FaPhone /> Téléphone direct :</strong> {selectedLivreur.telephone}</p>
            <p style={{ margin: "4px 0" }}><strong><FaEnvelope /> Adresse e-mail :</strong> {selectedLivreur.email}</p>
          </div>
        </div>
      )}

      {/* Barre de recherche et Bouton Action */}
      <div className="actions-bar" style={{ display: "flex", gap: "16px", alignItems: "center", marginBottom: "20px", flexWrap: "wrap" }}>
        <div className="search-bar" style={{ display: "flex", alignItems: "center", backgroundColor: "#fff", border: "1px solid #e2e8f0", borderRadius: "6px", padding: "4px 12px", flex: 1, minWidth: "280px" }}>
          <FaSearch style={{ color: "#28a458" }} />
          <input
            type="text"
            placeholder="Rechercher un livreur par nom, téléphone, email..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            style={{ width: "100%", border: "none", padding: "10px 12px", outline: "none", fontSize: "0.95rem" }}
          />
          <FaSync
            className={`refresh-icon ${loading ? "fa-spin" : ""}`}
            onClick={loadData}
            style={{ cursor: "pointer", color: "#28a458", transition: "transform 0.2s" }}
          />
        </div>
        
        <button 
          className="btn-primary" 
          onClick={() => { setErrorMessage(""); setIsAddModalOpen(true); }}
          style={{ display: "flex", alignItems: "center", gap: "8px", padding: "12px 20px", backgroundColor: "#28a458", color: "#fff", border: "none", borderRadius: "6px", fontWeight: "600", cursor: "pointer", boxShadow: "0 2px 4px rgba(40,164,88,0.15)", whiteSpace: "nowrap" }}
        >
          <FaPlus /> Ajouter un livreur
        </button>
      </div>

      {/* Tableau Restructuré */}
      <div className="table-container" style={{ backgroundColor: "#fff", borderRadius: "8px", boxShadow: "0 1px 3px rgba(0,0,0,0.05)", overflow: "hidden", border: "1px solid #e2e8f0" }}>
        {loading ? (
          <div className="loading-state" style={{ padding: "40px", textAlign: "center" }}>
            <div className="loading-spinner" style={{ margin: "0 auto 10px auto", width: "30px", height: "30px", border: "3px solid #f3f3f3", borderTop: "3px solid #28a458", borderRadius: "50%", animation: "spin 1s linear infinite" }}></div>
            <p style={{ color: "#718096" }}>Récupération des prestataires...</p>
          </div>
        ) : filteredLivreurs.length > 0 ? (
          <table className="data-table" style={{ width: "100%", borderCollapse: "collapse", textAlign: "left" }}>
            <thead>
              <tr style={{ backgroundColor: "#f8f9fa", borderBottom: "2px solid #edf2f7" }}>
                <th style={{ padding: "16px", color: "#4a5568", fontWeight: "600", fontSize: "0.9rem" }}>Nom du Livreur</th>
                <th style={{ padding: "16px", color: "#4a5568", fontWeight: "600", fontSize: "0.9rem" }}>Téléphone</th>
                <th style={{ padding: "16px", color: "#4a5568", fontWeight: "600", fontSize: "0.9rem" }}>Email</th>
                <th style={{ padding: "16px", color: "#4a5568", fontWeight: "600", fontSize: "0.9rem" }}>Statut</th>
                <th style={{ padding: "16px", color: "#4a5568", fontWeight: "600", fontSize: "0.9rem", textAlign: "right" }}>Actions</th>
              </tr>
            </thead>
            <tbody>
              {filteredLivreurs.map((l) => (
                <tr 
                  key={l.id || l.numLivreur}
                  onClick={() => setSelectedLivreur(l)}
                  style={{ 
                    cursor: "pointer", 
                    borderBottom: "1px solid #edf2f7",
                    backgroundColor: selectedLivreur?.id === l.id || selectedLivreur?.numLivreur === l.numLivreur ? "#f0fff4" : "transparent",
                    transition: "background-color 0.2s ease"
                  }}
                  className="table-row-hover"
                >
                  <td style={{ padding: "16px", fontWeight: "600", color: "#2d3748" }}>{l.nom}</td>
                  <td style={{ padding: "16px", color: "#4a5568", fontSize: "0.95rem" }}>
                    <div style={{ display: "flex", alignItems: "center", gap: "8px" }}>
                      <FaPhone size={12} color="#a0aec0" /> {l.telephone}
                    </div>
                  </td>
                  <td style={{ padding: "16px", color: "#4a5568", fontSize: "0.95rem" }}>
                    <div style={{ display: "flex", alignItems: "center", gap: "8px" }}>
                      <FaEnvelope size={12} color="#a0aec0" /> {l.email}
                    </div>
                  </td>
                  <td style={{ padding: "16px" }}>
                    <span className="status" style={{ fontSize: "0.8rem", padding: "4px 8px", borderRadius: "4px", fontWeight: "600", ...getStatusStyle(l.statut || 'disponible') }}>
                      {l.statut ? l.statut.replace('_', ' ') : 'disponible'}
                    </span>
                  </td>
                  {/* Colonne Actions */}
                  <td style={{ padding: "16px", textAlign: "right" }}>
                    <div style={{ display: "flex", justifyContent: "flex-end", gap: "12px" }}>
                      <button 
                        onClick={(e) => openEditModal(e, l)}
                        style={{ background: "none", border: "none", cursor: "pointer", color: "#4a5568", transition: "color 0.2s" }}
                        title="Modifier"
                      >
                        <FaEdit size={16} onMouseOver={(e) => e.currentTarget.style.color = "#28a458"} onMouseOut={(e) => e.currentTarget.style.color = "#4a5568"} />
                      </button>
                      <button 
                        onClick={(e) => openDeleteModal(e, l)}
                        style={{ background: "none", border: "none", cursor: "pointer", color: "#4a5568", transition: "color 0.2s" }}
                        title="Supprimer"
                      >
                        <FaTrashAlt size={15} onMouseOver={(e) => e.currentTarget.style.color = "#e53e3e"} onMouseOut={(e) => e.currentTarget.style.color = "#4a5568"} />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        ) : (
          <div className="empty-table" style={{ padding: "60px 40px", textAlign: "center" }}>
            <FaInfoCircle size={36} color="#cbd5e0" />
            <p style={{ marginTop: "12px", color: "#718096", fontSize: "0.95rem" }}>Aucun livreur répertorié pour cette recherche.</p>
          </div>
        )}
      </div>

      {/* --- MODAL AJOUT (CREATE) --- */}
      {isAddModalOpen && (
        <div className="modal-overlay" style={{ position: "fixed", top: 0, left: 0, right: 0, bottom: 0, backgroundColor: "rgba(0,0,0,0.4)", display: "flex", alignItems: "center", justifyContent: "center", zIndex: 1000 }}>
          <div className="modal-content" style={{ backgroundColor: "#fff", padding: "24px", borderRadius: "8px", width: "100%", maxWidth: "500px", boxShadow: "0 20px 25px -5px rgba(0,0,0,0.1)" }}>
            <div className="modal-header" style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: "20px" }}>
              <h2 style={{ margin: 0, fontSize: "1.25rem", color: "#1a202c", display: "flex", alignItems: "center", gap: "8px" }}><FaPlus color="#28a458"/> Nouveau Livreur</h2>
              <button onClick={() => setIsAddModalOpen(false)} style={{ background: "none", border: "none", fontSize: "1.5rem", cursor: "pointer", color: "#718096" }}>×</button>
            </div>
            {errorMessage && <div style={{ color: "#e53e3e", backgroundColor: "#fff5f5", padding: "10px", borderRadius: "4px", marginBottom: "15px", fontSize: "0.85rem" }}>{errorMessage}</div>}
            <form onSubmit={handleSubmit} style={{ display: "flex", flexDirection: "column", gap: "14px" }}>
              <div>
                <label style={{ display: "block", fontSize: "0.85rem", fontWeight: "600", marginBottom: "4px", color: "#4a5568" }}>Nom complet *</label>
                <input type="text" name="nom" value={formData.nom} onChange={handleInputChange} required style={{ width: "100%", padding: "8px 12px", borderRadius: "4px", border: "1px solid #cbd5e0", outline: "none" }} />
              </div>
              <div>
                <label style={{ display: "block", fontSize: "0.85rem", fontWeight: "600", marginBottom: "4px", color: "#4a5568" }}>N° Téléphone *</label>
                <input type="text" name="telephone" value={formData.telephone} onChange={handleInputChange} required style={{ width: "100%", padding: "8px 12px", borderRadius: "4px", border: "1px solid #cbd5e0", outline: "none" }} />
              </div>
              <div>
                <label style={{ display: "block", fontSize: "0.85rem", fontWeight: "600", marginBottom: "4px", color: "#4a5568" }}>Adresse Email *</label>
                <input type="email" name="email" value={formData.email} onChange={handleInputChange} required style={{ width: "100%", padding: "8px 12px", borderRadius: "4px", border: "1px solid #cbd5e0", outline: "none" }} />
              </div>
              <div>
                <label style={{ display: "block", fontSize: "0.85rem", fontWeight: "600", marginBottom: "4px", color: "#4a5568" }}>Mot de passe *</label>
                <input type="password" name="password" value={formData.password} onChange={handleInputChange} required style={{ width: "100%", padding: "8px 12px", borderRadius: "4px", border: "1px solid #cbd5e0", outline: "none" }} />
              </div>
              
              <div style={{ display: "flex", justifyContent: "flex-end", gap: "10px", marginTop: "10px" }}>
                <button type="button" onClick={() => setIsAddModalOpen(false)} style={{ padding: "8px 16px", borderRadius: "4px", border: "1px solid #cbd5e0", background: "#fff", cursor: "pointer" }}>Annuler</button>
                <button type="submit" disabled={submitting} style={{ padding: "8px 16px", borderRadius: "4px", border: "none", background: "#28a458", color: "#fff", cursor: "pointer" }}>{submitting ? "Enregistrement..." : "Créer le compte"}</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* --- MODAL MODIFICATION (EDIT) --- */}
      {isEditModalOpen && (
        <div className="modal-overlay" style={{ position: "fixed", top: 0, left: 0, right: 0, bottom: 0, backgroundColor: "rgba(0,0,0,0.4)", display: "flex", alignItems: "center", justifyContent: "center", zIndex: 1000 }}>
          <div className="modal-content" style={{ backgroundColor: "#fff", padding: "24px", borderRadius: "8px", width: "100%", maxWidth: "500px", boxShadow: "0 20px 25px -5px rgba(0,0,0,0.1)" }}>
            <div className="modal-header" style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: "20px" }}>
              <h2 style={{ margin: 0, fontSize: "1.25rem", color: "#1a202c", display: "flex", alignItems: "center", gap: "8px" }}><FaEdit color="#28a458"/> Modifier Livreur</h2>
              <button onClick={() => setIsEditModalOpen(false)} style={{ background: "none", border: "none", fontSize: "1.5rem", cursor: "pointer", color: "#718096" }}>×</button>
            </div>
            {errorMessage && <div style={{ color: "#e53e3e", backgroundColor: "#fff5f5", padding: "10px", borderRadius: "4px", marginBottom: "15px", fontSize: "0.85rem" }}>{errorMessage}</div>}
            <form onSubmit={handleEditSubmit} style={{ display: "flex", flexDirection: "column", gap: "14px" }}>
              <div>
                <label style={{ display: "block", fontSize: "0.85rem", fontWeight: "600", marginBottom: "4px", color: "#4a5568" }}>Nom complet</label>
                <input type="text" name="nom" value={editFormData.nom} onChange={handleEditInputChange} required style={{ width: "100%", padding: "8px 12px", borderRadius: "4px", border: "1px solid #cbd5e0", outline: "none" }} />
              </div>
              <div>
                <label style={{ display: "block", fontSize: "0.85rem", fontWeight: "600", marginBottom: "4px", color: "#4a5568" }}>N° Téléphone</label>
                <input type="text" name="telephone" value={editFormData.telephone} onChange={handleEditInputChange} required style={{ width: "100%", padding: "8px 12px", borderRadius: "4px", border: "1px solid #cbd5e0", outline: "none" }} />
              </div>
              <div>
                <label style={{ display: "block", fontSize: "0.85rem", fontWeight: "600", marginBottom: "4px", color: "#4a5568" }}>Adresse Email</label>
                <input type="email" name="email" value={editFormData.email} onChange={handleEditInputChange} required style={{ width: "100%", padding: "8px 12px", borderRadius: "4px", border: "1px solid #cbd5e0", outline: "none" }} />
              </div>
              <div>
                <label style={{ display: "block", fontSize: "0.85rem", fontWeight: "600", marginBottom: "4px", color: "#4a5568" }}>Statut de disponibilité</label>
                <select name="statut" value={editFormData.statut} onChange={handleEditInputChange} style={{ width: "100%", padding: "8px 12px", borderRadius: "4px", border: "1px solid #cbd5e0", outline: "none", backgroundColor: "#fff" }}>
                  <option value="disponible">Disponible</option>
                  <option value="en_livraison">En livraison</option>
                  <option value="indisponible">Indisponible</option>
                </select>
              </div>
              
              <div style={{ display: "flex", justifyContent: "flex-end", gap: "10px", marginTop: "10px" }}>
                <button type="button" onClick={() => setIsEditModalOpen(false)} style={{ padding: "8px 16px", borderRadius: "4px", border: "1px solid #cbd5e0", background: "#fff", cursor: "pointer" }}>Annuler</button>
                <button type="submit" disabled={submitting} style={{ padding: "8px 16px", borderRadius: "4px", border: "none", background: "#28a458", color: "#fff", cursor: "pointer" }}>{submitting ? "Mise à jour..." : "Enregistrer les modifications"}</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* --- MODAL CONFIRMATION SUPPRESSION (DELETE) --- */}
      {isDeleteModalOpen && (
        <div className="modal-overlay" style={{ position: "fixed", top: 0, left: 0, right: 0, bottom: 0, backgroundColor: "rgba(0,0,0,0.4)", display: "flex", alignItems: "center", justifyContent: "center", zIndex: 1000 }}>
          <div className="modal-content" style={{ backgroundColor: "#fff", padding: "24px", borderRadius: "8px", width: "100%", maxWidth: "420px", boxShadow: "0 20px 25px -5px rgba(0,0,0,0.1)", textAlign: "center" }}>
            <div style={{ color: "#e53e3e", marginBottom: "16px" }}>
              <FaTrashAlt size={40} />
            </div>
            <h2 style={{ margin: "0 0 10px 0", fontSize: "1.2rem", color: "#1a202c" }}>Supprimer le compte ?</h2>
            <p style={{ color: "#718096", fontSize: "0.9rem", margin: "0 0 24px 0" }}>
              Êtes-vous sûr de vouloir supprimer le livreur <strong>{livreurToAction?.nom}</strong> ? Cette action est irréversible.
            </p>
            <div style={{ display: "flex", justifyContent: "center", gap: "12px" }}>
              <button type="button" onClick={() => setIsDeleteModalOpen(false)} disabled={submitting} style={{ padding: "10px 20px", borderRadius: "4px", border: "1px solid #cbd5e0", background: "#fff", cursor: "pointer", fontWeight: "600" }}>Annuler</button>
              <button type="button" onClick={handleDeleteConfirm} disabled={submitting} style={{ padding: "10px 20px", borderRadius: "4px", border: "none", background: "#e53e3e", color: "#fff", cursor: "pointer", fontWeight: "600" }}>
                {submitting ? "Suppression..." : "Confirmer la suppression"}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default LivreursOnglet;