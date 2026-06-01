import React, { useState, useEffect } from "react";
import {
  FaSearch,
  FaSync,
  FaTruck,
  FaMapMarkerAlt,
  FaWeightHanging,
  FaUserTie,
  FaEnvelope,
  FaPhone,
  FaCar,
  FaInfoCircle,
  FaPlus,
  FaTimes,
} from "react-icons/fa";
import { useNavigate } from "react-router-dom";
import { fetchLivreurs, createLivreur } from "../../../services/LivreurService";
import "../../../styles/back-office/global.css";
import "../../../styles/back-office/tableau.css";
import "../../../styles/back-office/modal.css";

const Livreurs = () => {
  const navigate = useNavigate();
  const [livreurs, setLivreurs] = useState([]);
  const [searchTerm, setSearchTerm] = useState("");
  const [loading, setLoading] = useState(true);
  const [selectedLivreur, setSelectedLivreur] = useState(null);
  const [isAddModalOpen, setIsAddModalOpen] = useState(false);
  
  const [formData, setFormData] = useState({
    nom: "",
    email: "",
    password: "",
    contact: "",
    matricule_vehicule: "",
  });
  const [submitting, setSubmitting] = useState(false);
  const [errorMessage, setErrorMessage] = useState("");

  const loadData = async () => {
    try {
      setLoading(true);
      const data = await fetchLivreurs();
      setLivreurs(data);
    } catch (err) {
      console.error("Erreur de chargement des livreurs:", err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
 //   loadData();
  }, []);

  const filteredLivreurs = livreurs.filter(
    (l) =>
      l.nom?.toLowerCase().includes(searchTerm.toLowerCase()) ||
      l.email?.toLowerCase().includes(searchTerm.toLowerCase()) ||
      l.contact?.toLowerCase().includes(searchTerm.toLowerCase())
  );

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    setErrorMessage("");

    if (!formData.nom || !formData.email || !formData.password || !formData.contact) {
      setErrorMessage("Tous les champs obligatoires doivent être remplis.");
      setSubmitting(false);
      return;
    }

    try {
      await createLivreur(formData);
      setIsAddModalOpen(false);
      setFormData({
        nom: "",
        email: "",
        password: "",
        contact: "",
        matricule_vehicule: "",
      });
      await loadData();
      alert("Livreur ajouté avec succès !");
    } catch (err) {
      console.error("Erreur création livreur:", err);
      setErrorMessage(
        err.response?.data?.message || "Erreur lors de l'ajout du livreur. Vérifiez l'email (unique ?)"
      );
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="page-container" style={{ padding: "24px", maxWidth: "1400px", margin: "0 auto", fontFamily: "system-ui, sans-serif" }}>
      
      {/* Header avec bouton d'action principal */}
      <div className="page-header" style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: "24px" }}>
        <div>
          <h1 style={{ display: "flex", alignItems: "center", gap: "12px", fontSize: "1.85rem", color: "#1a202c", fontWeight: "700", margin: 0 }}>
            <FaUserTie style={{ color: "#28a458" }} /> Annuaire des Livreurs
          </h1>
          <p style={{ color: "#718096", margin: "4px 0 0 0", fontSize: "0.95rem" }}>
            Gérez vos équipes de coursiers et prestataires de livraison.
          </p>
        </div>
        <button 
          className="btn-primary" 
          onClick={() => setIsAddModalOpen(true)}
          style={{ display: "flex", alignItems: "center", gap: "8px", padding: "10px 18px", backgroundColor: "#28a458", color: "#fff", border: "none", borderRadius: "6px", fontWeight: "600", cursor: "pointer", boxShadow: "0 2px 4px rgba(40,164,88,0.2)" }}
        >
          <FaPlus /> Nouveau Livreur
        </button>
      </div>

      {/* Onglets épurés */}
      <div className="navigation-tabs" style={{ display: "flex", gap: "8px", borderBottom: "2px solid #edf2f7", marginBottom: "24px" }}>
        <button className="tab-inactive" onClick={() => navigate("/admin/livraisons")} style={{ display: "flex", alignItems: "center", gap: "8px", padding: "12px 16px", border: "none", background: "none", cursor: "pointer", fontSize: "0.95rem", fontWeight: "600", color: "#718096", borderBottom: "3px solid transparent", marginBottom: "-2px" }}>
          <FaTruck /> Livraisons
        </button>
        <button className="tab-active" style={{ display: "flex", alignItems: "center", gap: "8px", padding: "12px 16px", border: "none", background: "none", cursor: "pointer", fontSize: "0.95rem", fontWeight: "600", color: "#28a458", borderBottom: "3px solid #28a458", marginBottom: "-2px" }}>
          <FaUserTie /> Livreurs
        </button>
        <button className="tab-inactive" onClick={() => navigate("/admin/livraisons/frais")} style={{ display: "flex", alignItems: "center", gap: "8px", padding: "12px 16px", border: "none", background: "none", cursor: "pointer", fontSize: "0.95rem", fontWeight: "600", color: "#718096", borderBottom: "3px solid transparent", marginBottom: "-2px" }}>
          <FaWeightHanging /> Frais
        </button>
        <button className="tab-inactive" onClick={() => navigate("/admin/livraisons/lieux")} style={{ display: "flex", alignItems: "center", gap: "8px", padding: "12px 16px", border: "none", background: "none", cursor: "pointer", fontSize: "0.95rem", fontWeight: "600", color: "#718096", borderBottom: "3px solid transparent", marginBottom: "-2px" }}>
          <FaMapMarkerAlt /> Lieux
        </button>
      </div>

      {/* Focus Carte Livreur Sélectionné */}
      {selectedLivreur && (
        <div className="selected-card" style={{ animation: "fadeIn 0.2s ease", backgroundColor: "#f8f9fa", borderLeft: "4px solid #28a458", padding: "16px 20px", borderRadius: "6px", marginBottom: "20px", boxShadow: "0 1px 3px rgba(0,0,0,0.05)", position: "relative" }}>
          <button onClick={() => setSelectedLivreur(null)} style={{ position: "absolute", top: "12px", right: "12px", background: "none", border: "none", cursor: "pointer", color: "#a0aec0" }}>
            <FaTimes size={16} />
          </button>
          <h3 style={{ margin: "0 0 12px 0", color: "#2d3748", display: "flex", alignItems: "center", gap: "8px", fontSize: "1.1rem" }}>
            <FaUserTie color="#28a458"/> Détails de {selectedLivreur.nom}
          </h3>
          <div style={{ display: "flex", gap: "40px", flexWrap: "wrap", fontSize: "0.9rem", color: "#4a5568" }}>
            <p style={{ margin: "4px 0" }}><strong><FaCar /> Véhicule Matricule :</strong> <code style={{ backgroundColor: "#e2e8f0", padding: "2px 6px", borderRadius: "4px" }}>{selectedLivreur.matricule_vehicule || "Non renseigné"}</code></p>
            <p style={{ margin: "4px 0" }}><strong><FaPhone /> Contact direct :</strong> {selectedLivreur.contact}</p>
            <p style={{ margin: "4px 0" }}><strong><FaEnvelope /> Adresse e-mail :</strong> {selectedLivreur.email}</p>
          </div>
        </div>
      )}

      {/* Barre de recherche */}
      <div className="search-container" style={{ marginBottom: "20px" }}>
        <div className="search-bar" style={{ display: "flex", alignItems: "center", backgroundColor: "#fff", border: "1px solid #e2e8f0", borderRadius: "6px", padding: "4px 12px" }}>
          <FaSearch style={{ color: "#28a458" }} />
          <input
            type="text"
            placeholder="Rechercher par nom, téléphone, email..."
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
      </div>

      {/* Tableau Restructuré (Nom, Téléphone, Email, Statut) */}
      <div className="table-container" style={{ backgroundColor: "#fff", borderRadius: "8px", boxShadow: "0 1px 3px rgba(0,0,0,0.05)", overflow: "hidden", border: "1px solid #e2e8f0" }}>
        {loading ? (
          <div className="loading-state" style={{ padding: "40px", textAlign: "center" }}>
            <div className="loading-spinner" style={{ margin: "0 auto 10px auto", width: "30px", height: "30px", border: "3px solid #f3f3f3", borderTop: "3px solid #28a458", borderRadius: "50%", animation: "spin 1s linear infinite" }}></div>
            <p style={{ color: "#718096" }}>Récupération des données...</p>
          </div>
        ) : filteredLivreurs.length > 0 ? (
          <table className="data-table" style={{ width: "100%", borderCollapse: "collapse", textAlign: "left" }}>
            <thead>
              <tr style={{ backgroundColor: "#f8f9fa", borderBottom: "2px solid #edf2f7" }}>
                <th style={{ padding: "16px", color: "#4a5568", fontWeight: "600", fontSize: "0.9rem" }}>Nom du Livreur</th>
                <th style={{ padding: "16px", color: "#4a5568", fontWeight: "600", fontSize: "0.9rem" }}>Téléphone</th>
                <th style={{ padding: "16px", color: "#4a5568", fontWeight: "600", fontSize: "0.9rem" }}>Email</th>
                <th style={{ padding: "16px", color: "#4a5568", fontWeight: "600", fontSize: "0.9rem" }}>Statut</th>
              </tr>
            </thead>
            <tbody>
              {filteredLivreurs.map((l) => (
                <tr 
                  key={l.numLivreur || l.id}
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
                      <FaPhone size={12} color="#a0aec0" /> {l.contact}
                    </div>
                  </td>
                  <td style={{ padding: "16px", color: "#4a5568", fontSize: "0.95rem" }}>
                    <div style={{ display: "flex", alignItems: "center", gap: "8px" }}>
                      <FaEnvelope size={12} color="#a0aec0" /> {l.email}
                    </div>
                  </td>
                  <td style={{ padding: "16px" }}>
                    <span className="status livrée" style={{ fontSize: "0.8rem", padding: "4px 8px", borderRadius: "4px", backgroundColor: "#c6f6d5", color: "#22543d", fontWeight: "600" }}>
                      Actif
                    </span>
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

      {/* Modal d'ajout de Livreur */}
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
                <input type="text" name="nom" value={formData.nom} onChange={handleInputChange} required className="form-control" style={{ width: "100%", padding: "8px 12px", borderRadius: "4px", border: "1px solid #cbd5e0" }} />
              </div>
              <div>
                <label style={{ display: "block", fontSize: "0.85rem", fontWeight: "600", marginBottom: "4px", color: "#4a5568" }}>N° Téléphone *</label>
                <input type="text" name="contact" value={formData.contact} onChange={handleInputChange} required className="form-control" style={{ width: "100%", padding: "8px 12px", borderRadius: "4px", border: "1px solid #cbd5e0" }} />
              </div>
              <div>
                <label style={{ display: "block", fontSize: "0.85rem", fontWeight: "600", marginBottom: "4px", color: "#4a5568" }}>Adresse Email *</label>
                <input type="email" name="email" value={formData.email} onChange={handleInputChange} required className="form-control" style={{ width: "100%", padding: "8px 12px", borderRadius: "4px", border: "1px solid #cbd5e0" }} />
              </div>
              <div>
                <label style={{ display: "block", fontSize: "0.85rem", fontWeight: "600", marginBottom: "4px", color: "#4a5568" }}>Mot de passe *</label>
                <input type="password" name="password" value={formData.password} onChange={handleInputChange} required className="form-control" style={{ width: "100%", padding: "8px 12px", borderRadius: "4px", border: "1px solid #cbd5e0" }} />
              </div>
              <div>
                <label style={{ display: "block", fontSize: "0.85rem", fontWeight: "600", marginBottom: "4px", color: "#4a5568" }}>Matricule Véhicule</label>
                <input type="text" name="matricule_vehicule" value={formData.matricule_vehicule} onChange={handleInputChange} className="form-control" placeholder="Ex: 1234 WWT" style={{ width: "100%", padding: "8px 12px", borderRadius: "4px", border: "1px solid #cbd5e0" }} />
              </div>
              <div style={{ display: "flex", justifyContent: "flex-end", gap: "10px", marginTop: "10px" }}>
                <button type="button" onClick={() => setIsAddModalOpen(false)} style={{ padding: "8px 16px", borderRadius: "4px", border: "1px solid #cbd5e0", background: "#fff", cursor: "pointer" }}>Annuler</button>
                <button type="submit" disabled={submitting} style={{ padding: "8px 16px", borderRadius: "4px", border: "none", background: "#28a458", color: "#fff", cursor: "pointer" }}>{submitting ? "Enregistrement..." : "Créer le compte"}</button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};

export default Livreurs;