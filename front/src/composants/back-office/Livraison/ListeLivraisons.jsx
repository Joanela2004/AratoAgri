import React from "react";
import { FaSearch, FaFilter, FaSync, FaCalendarAlt, FaMapMarkerAlt, FaCheckCircle, FaEdit } from "react-icons/fa";
import DatePicker from "react-datepicker";
import { fr } from "date-fns/locale";

const ListeLivraisons = ({
  filteredLivraisons,
  searchTerm,
  setSearchTerm,
  showAdvancedFilters,
  setShowAdvancedFilters,
  reinitialiserFiltres,
  filtreStatut,
  setFiltreStatut,
  filtreTransporteur,
  setFiltreTransporteur,
  transporteursUniques,
  filtreDateMin,
  setFiltreDateMin,
  filtreDateMax,
  setFiltreDateMax,
  hasActiveFilters,
  setCurrentLivraison,
  setIsModalOpen
}) => {
  return (
    <>
      {/* Recherche */}
      <div className="search-container">
        <div className="search-bar">
          <FaSearch style={{ marginLeft: "8px", color: "#28a458" }} />
          <input
            type="text"
            placeholder="Rechercher par commande, transporteur, référence ou client..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
          />
          <button
            className={`filter-toggle ${showAdvancedFilters ? "active" : ""}`}
            onClick={() => setShowAdvancedFilters(!showAdvancedFilters)}
            style={{ border: "none", background: "white", color: "#28a458", paddingRight: "10px" }}
          >
            <FaFilter />
          </button>
          <FaSync onClick={reinitialiserFiltres} style={{ marginRight: "8px", color: "#28a458", cursor: "pointer" }} title="Réinitialiser" />
        </div>
      </div>

      {/* Filtres Avancés */}
      {showAdvancedFilters && (
        <div className="filters-container">
          <div className="filters-row">
            <div className="filter-group">
              <label>Statut</label>
              <select className="form-control" value={filtreStatut} onChange={(e) => setFiltreStatut(e.target.value)}>
                <option value="tous">Tous les statuts</option>
                <option value="en cours">En cours</option>
                <option value="livrée">Livrée</option>
              </select>
            </div>
            <div className="filter-group">
              <label>Transporteur</label>
              <select className="form-control" value={filtreTransporteur} onChange={(e) => setFiltreTransporteur(e.target.value)}>
                <option value="">Tous les transporteurs</option>
                {transporteursUniques.map((t, i) => <option key={i} value={t}>{t}</option>)}
              </select>
            </div>
            <div className="filter-group">
              <label><FaCalendarAlt /> Date min</label>
              <DatePicker
                selected={filtreDateMin ? new Date(filtreDateMin) : null}
                onChange={(date) => setFiltreDateMin(date ? date.toISOString().split("T")[0] : "")}
                dateFormat="dd/MM/yyyy" locale={fr} placeholderText="jj/mm/aaaa" className="form-control" isClearable
              />
            </div>
            <div className="filter-group">
              <label><FaCalendarAlt /> Date max</label>
              <DatePicker
                selected={filtreDateMax ? new Date(filtreDateMax) : null}
                onChange={(date) => setFiltreDateMax(date ? date.toISOString().split("T")[0] : "")}
                dateFormat="dd/MM/yyyy" locale={fr} placeholderText="jj/mm/aaaa" className="form-control" isClearable
              />
            </div>
          </div>
        </div>
      )}

      {/* Tableau */}
      <div className="table-container">
        {filteredLivraisons.length > 0 ? (
          <table className="data-table">
            <thead>
              <tr>
                <th>Commande</th>
                <th>Client</th>
                <th>Transporteur</th>
                <th>Référence Colis</th>
                <th>Lieu de livraison</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {filteredLivraisons.map((livraison) => (
                <tr key={livraison.numLivraison}>
                  <td>{livraison.commande?.referenceCommande || livraison.numCommande}</td>
                  <td>{livraison.commande?.utilisateur?.nomUtilisateur || "-"}</td>
                  <td>{livraison.transporteur || "-"}</td>
                  <td><code>{livraison.referenceColis || "-"}</code></td>
                  <td>
                    <div style={{ display: "flex", alignItems: "center", gap: "8px" }}>
                      <FaMapMarkerAlt style={{ color: "#dc3545" }} />
                      <span>{livraison.lieuLivraison || "-"}</span>
                    </div>
                  </td>
                  <td>
                    <span className={`status ${livraison.statutLivraison === "livrée" ? "livrée" : "en-cours"}`}>
                      {livraison.statutLivraison === "livrée" ? <><FaCheckCircle /> Livrée</> : "En cours"}
                    </span>
                  </td>
                  <td>
                    {livraison.statutLivraison === "en cours" ? (
                      <button className="edit" onClick={() => { setCurrentLivraison(livraison); setIsModalOpen(true); }}>
                        <FaEdit /> Modifier
                      </button>
                    ) : <span style={{ color: "#666", fontStyle: "italic" }}>Terminée</span>}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        ) : (
          <div className="empty-table" style={{ textAlign: "center", padding: "40px" }}>
            <h3>{hasActiveFilters ? "Aucun résultat pour ces filtres" : "Aucune livraison expédiée"}</h3>
          </div>
        )}
      </div>
    </>
  );
};

export default ListeLivraisons;