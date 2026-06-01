import React, { useEffect, useState } from "react";
import { fetchLivraisons } from "../../../services/livraisonService";
import { 
  FaTruck, 
  FaUserTie, 
  FaWeightHanging, 
  FaMapMarkerAlt, 
  FaClock, 
  FaCheckCircle, 
  FaBoxes 
} from "react-icons/fa";

// Importation des sous-composants d'onglets
import ListeLivraisons from "./ListeLivraisons";
import LivreursOnglet from "./LivreursOnglet";
import { FraisOnglet, LieuxOnglet } from "./FraisOnglet"; 
import LivraisonModal from "./LivraisonModal"; 

import "../../../styles/back-office/global.css";
import "../../../styles/back-office/tableau.css";
import "../../../styles/back-office/modal.css";

const Livraisons = () => {
  const [activeTab, setActiveTab] = useState("livraisons");
  const [livraisons, setLivraisons] = useState([]);
  const [searchTerm, setSearchTerm] = useState("");
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [currentLivraison, setCurrentLivraison] = useState(null);
  const [loading, setLoading] = useState(true);
  const [showAdvancedFilters, setShowAdvancedFilters] = useState(false);
  
  const [filtreStatut, setFiltreStatut] = useState("tous");
  const [filtreDateMin, setFiltreDateMin] = useState("");
  const [filtreDateMax, setFiltreDateMax] = useState("");
  const [filtreTransporteur, setFiltreTransporteur] = useState("");

  const loadData = async () => {
    try {
      setLoading(true);
      const data = await fetchLivraisons();
      const sortedData = data.sort((a, b) => {
        const dateA = a.commande?.dateCommande ? new Date(a.commande.dateCommande) : new Date(0);
        const dateB = b.commande?.dateCommande ? new Date(b.commande.dateCommande) : new Date(0);
        return dateB - dateA;
      });
      setLivraisons(sortedData);
    } catch (error) {
      console.error("Erreur lors du chargement des livraisons:", error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, []);

  const transporteursUniques = [...new Set(livraisons.filter((l) => l.transporteur).map((l) => l.transporteur))];

  // Filtrage des livraisons
  const filteredLivraisons = livraisons.filter((livraison) => {
    if (!livraison.dateExpedition) return false;

    const searchMatch =
      (livraison.numCommande?.toString() || "").includes(searchTerm.toLowerCase()) ||
      (livraison.transporteur?.toLowerCase() || "").includes(searchTerm.toLowerCase()) ||
      (livraison.referenceColis?.toLowerCase() || "").includes(searchTerm.toLowerCase()) ||
      (livraison.lieuLivraison?.toLowerCase() || "").includes(searchTerm.toLowerCase());

    const statutMatch = filtreStatut === "tous" || livraison.statutLivraison === filtreStatut;
    const transporteurMatch = !filtreTransporteur || livraison.transporteur?.toLowerCase() === filtreTransporteur.toLowerCase();

    return searchMatch && statutMatch && transporteurMatch;
  });

  // Calculs des compteurs pour les KPI Cards
  const totalExpediees = livraisons.filter(l => l.dateExpedition).length;
  const enCoursCount = livraisons.filter(l => l.dateExpedition && l.statutLivraison === "en cours").length;
  const livreesCount = livraisons.filter(l => l.dateExpedition && l.statutLivraison === "livrée").length;

  const reinitialiserFiltres = () => {
    setSearchTerm("");
    setFiltreStatut("tous");
    setFiltreDateMin("");
    setFiltreDateMax("");
    setFiltreTransporteur("");
  };

  const renderTabContent = () => {
    switch (activeTab) {
      case "livraisons":
        return (
          <ListeLivraisons
            filteredLivraisons={filteredLivraisons}
            searchTerm={searchTerm}
            setSearchTerm={setSearchTerm}
            showAdvancedFilters={showAdvancedFilters}
            setShowAdvancedFilters={setShowAdvancedFilters}
            reinitialiserFiltres={reinitialiserFiltres}
            filtreStatut={filtreStatut}
            setFiltreStatut={setFiltreStatut}
            filtreTransporteur={filtreTransporteur}
            setFiltreTransporteur={setFiltreTransporteur}
            transporteursUniques={transporteursUniques}
            filtreDateMin={filtreDateMin}
            setFiltreDateMin={setFiltreDateMin}
            filtreDateMax={filtreDateMax}
            setFiltreDateMax={setFiltreDateMax}
            hasActiveFilters={searchTerm || filtreStatut !== "tous" || filtreTransporteur}
            setCurrentLivraison={setCurrentLivraison}
            setIsModalOpen={setIsModalOpen}
          />
        );
      case "livreurs":
        return <LivreursOnglet />;
      case "frais":
        return <FraisOnglet />;
      case "lieux":
        return <LieuxOnglet />;
      default:
        return null;
    }
  };

  if (loading) {
    return (
      <div  style={{ display: "flex", justifyContent: "center", alignItems: "center", height: "100vh" }}>
        <div className="loading-state" style={{ textAlign: "center" }}>
          <div className="loading-spinner" style={{ width: "40px", height: "40px", border: "4px solid #f3f3f3", borderTop: "4px solid #28a458", borderRadius: "50%", animation: "spin 1s linear infinite" }}></div>
          <p style={{ marginTop: "15px", color: "#666", fontWeight: "500" }}>Chargement du panneau logistique...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="page-container" style={{ padding: "24px", maxWidth: "1400px", margin: "0 auto", fontFamily: "system-ui, sans-serif" }}>
      
      {/* En-tête de page moderne */}
      <div className="page-header" style={{ marginBottom: "24px" }}>
        <h1 style={{ display: "flex", alignItems: "center", gap: "12px", fontSize: "1.85rem", color: "#1a202c", fontWeight: "700", margin: 0 }}>
          <FaTruck style={{ color: "#28a458" }} /> Logistique & Expéditions
        </h1>
        <p style={{ color: "#718096", margin: "4px 0 0 0", fontSize: "0.95rem" }}>
          Suivez l'état de transit de vos colis, gérez vos coursiers partenaires et vos grilles tarifaires.
        </p>
      </div>

      {/* Cartes de statistiques (KPI Dashboard) */}
      <div className="kpi-dashboard-grid" style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(240px, 1fr))", gap: "16px", marginBottom: "30px" }}>
        
        <div style={{ backgroundColor: "#ffffff", padding: "20px", borderRadius: "10px", boxShadow: "0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.1)", display: "flex", alignItems: "center", gap: "16px", border: "1px solid #e2e8f0" }}>
          <div style={{ width: "48px", height: "48px", borderRadius: "8px", backgroundColor: "#ebf8ff", display: "flex", alignItems: "center", justifyContent: "center", color: "#3182ce" }}>
            <FaBoxes size={22} />
          </div>
          <div>
            <span style={{ display: "block", color: "#718096", fontSize: "0.85rem", fontWeight: "600", textTransform: "uppercase" }}>Total Expédié</span>
            <span style={{ fontSize: "1.6rem", fontWeight: "700", color: "#2d3748" }}>{totalExpediees}</span>
          </div>
        </div>

        <div style={{ backgroundColor: "#ffffff", padding: "20px", borderRadius: "10px", boxShadow: "0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.1)", display: "flex", alignItems: "center", gap: "16px", border: "1px solid #e2e8f0" }}>
          <div style={{ width: "48px", height: "48px", borderRadius: "8px", backgroundColor: "#fffaf0", display: "flex", alignItems: "center", justifyContent: "center", color: "#dd6b20" }}>
            <FaClock size={22} />
          </div>
          <div>
            <span style={{ display: "block", color: "#718096", fontSize: "0.85rem", fontWeight: "600", textTransform: "uppercase" }}>En cours de Transit</span>
            <span style={{ fontSize: "1.6rem", fontWeight: "700", color: "#2d3748" }}>{enCoursCount}</span>
          </div>
        </div>

        <div style={{ backgroundColor: "#ffffff", padding: "20px", borderRadius: "10px", boxShadow: "0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.1)", display: "flex", alignItems: "center", gap: "16px", border: "1px solid #e2e8f0" }}>
          <div style={{ width: "48px", height: "48px", borderRadius: "8px", backgroundColor: "#f0fff4", display: "flex", alignItems: "center", justifyContent: "center", color: "#38a169" }}>
            <FaCheckCircle size={22} />
          </div>
          <div>
            <span style={{ display: "block", color: "#718096", fontSize: "0.85rem", fontWeight: "600", textTransform: "uppercase" }}>Livraisons Réussies</span>
            <span style={{ fontSize: "1.6rem", fontWeight: "700", color: "#2d3748" }}>{livreesCount}</span>
          </div>
        </div>

      </div>

      {/* Onglets de Navigation avec style épuré et aligné */}
      <div className="navigation-tabs" style={{ display: "flex", gap: "8px", borderBottom: "2px solid #edf2f7", marginBottom: "24px", paddingBottom: "1px" }}>
        <button 
          className={activeTab === "livraisons" ? "tab-active" : "tab-inactive"} 
          onClick={() => setActiveTab("livraisons")}
          style={{ display: "flex", alignItems: "center", gap: "8px", padding: "12px 16px", border: "none", background: "none", cursor: "pointer", fontSize: "0.95rem", fontWeight: "600", color: activeTab === "livraisons" ? "#28a458" : "#718096", borderBottom: activeTab === "livraisons" ? "3px solid #28a458" : "3px solid transparent", transition: "all 0.2s ease", marginBottom: "-3px" }}
        >
          <FaTruck /> Livraisons
          <span style={{ fontSize: "0.75rem", padding: "2px 6px", borderRadius: "10px", backgroundColor: activeTab === "livraisons" ? "#e6fffa" : "#edf2f7", color: activeTab === "livraisons" ? "#28a458" : "#718096", marginLeft: "4px" }}>{totalExpediees}</span>
        </button>
        
        <button 
          className={activeTab === "livreurs" ? "tab-active" : "tab-inactive"} 
          onClick={() => setActiveTab("livreurs")}
          style={{ display: "flex", alignItems: "center", gap: "8px", padding: "12px 16px", border: "none", background: "none", cursor: "pointer", fontSize: "0.95rem", fontWeight: "600", color: activeTab === "livreurs" ? "#28a458" : "#718096", borderBottom: activeTab === "livreurs" ? "3px solid #28a458" : "3px solid transparent", transition: "all 0.2s ease", marginBottom: "-3px" }}
        >
          <FaUserTie /> Livreurs
        </button>

        <button 
          className={activeTab === "frais" ? "tab-active" : "tab-inactive"} 
          onClick={() => setActiveTab("frais")}
          style={{ display: "flex", alignItems: "center", gap: "8px", padding: "12px 16px", border: "none", background: "none", cursor: "pointer", fontSize: "0.95rem", fontWeight: "600", color: activeTab === "frais" ? "#28a458" : "#718096", borderBottom: activeTab === "frais" ? "3px solid #28a458" : "3px solid transparent", transition: "all 0.2s ease", marginBottom: "-3px" }}
        >
          <FaWeightHanging /> Frais
        </button>

        <button 
          className={activeTab === "lieux" ? "tab-active" : "tab-inactive"} 
          onClick={() => setActiveTab("lieux")}
          style={{ display: "flex", alignItems: "center", gap: "8px", padding: "12px 16px", border: "none", background: "none", cursor: "pointer", fontSize: "0.95rem", fontWeight: "600", color: activeTab === "lieux" ? "#28a458" : "#718096", borderBottom: activeTab === "lieux" ? "3px solid #28a458" : "3px solid transparent", transition: "all 0.2s ease", marginBottom: "-3px" }}
        >
          <FaMapMarkerAlt /> Lieux de livraison
        </button>
      </div>

      {/* Zone d'affichage du contenu dynamique de l'onglet */}
      <div className="tab-content-container" style={{ animation: "fadeIn 0.3s ease" }}>
        {renderTabContent()}
      </div>

      {/* Modal global de modification */}
      {isModalOpen && currentLivraison && (
        <LivraisonModal
          isOpen={isModalOpen}
          onClose={() => { setIsModalOpen(false); setCurrentLivraison(null); }}
          livraison={currentLivraison}
          onSave={loadData}
        />
      )}
    </div>
  );
};

export default Livraisons;