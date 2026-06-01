import React, { useEffect, useState } from "react";
import { FaEdit, FaCheckCircle } from "react-icons/fa";
import { updateLivraison } from "../../../services/livraisonService";
import { updateCommandeAdmin } from "../../../services/commandeService";

const LivraisonModal = ({ isOpen, onClose, livraison, onSave }) => {
  const [formData, setFormData] = useState({});
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (livraison) {
      setFormData({
        ...livraison,
        transporteur: livraison.transporteur || "",
        referenceColis: livraison.referenceColis || "",
        lieuLivraison: livraison.lieuLivraison || "",
        contactTransporteur: livraison.contactTransporteur || "",
        statutLivraison: livraison.statutLivraison || "en cours",
      });
    }
  }, [livraison]);

  if (!isOpen) return null;

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    try {
      const updatedData = { ...formData };

      if (
        updatedData.statutLivraison === "livrée" &&
        !updatedData.dateLivraison
      ) {
        updatedData.dateLivraison = new Date()
          .toISOString()
          .slice(0, 19)
          .replace("T", " ");
      }

      await updateLivraison(updatedData.numLivraison, updatedData);

      if (updatedData.statutLivraison === "livrée") {
        await updateCommandeAdmin(updatedData.numCommande, {
          statut: "livrée",
          dateLivraison: updatedData.dateLivraison,
        });
      }

      onSave();
      onClose();
    } catch (error) {
      console.error("Erreur lors de la mise à jour:", error);
      alert("Une erreur est survenue lors de la mise à jour de la livraison.");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="modal-overlay">
      <div className="modal-content" style={{ maxWidth: "600px" }}>
        <div className="modal-header">
          <h2>
            <FaEdit style={{ color: "var(--color-primary, #28a458)" }} />
            Modifier la Livraison #{livraison?.numCommande}
          </h2>
          <button className="modal-close" onClick={onClose}>
            ×
          </button>
        </div>

        <form onSubmit={handleSubmit} className="modal-form">
          <div className="modal-body">
            <div className="form-row">
              <div className="form-group">
                <label>Transporteur</label>
                <input
                  type="text"
                  name="transporteur"
                  value={formData.transporteur || ""}
                  onChange={handleChange}
                  required
                  placeholder="Nom du transporteur"
                  className="form-control"
                />
              </div>
              <div className="form-group">
                <label>Référence Colis</label>
                <input
                  type="text"
                  name="referenceColis"
                  value={formData.referenceColis || ""}
                  onChange={handleChange}
                  required
                  placeholder="Numéro de suivi"
                  className="form-control"
                />
              </div>
            </div>

            <div className="form-row">
              <div className="form-group">
                <label>Lieu de livraison</label>
                <input
                  type="text"
                  name="lieuLivraison"
                  value={formData.lieuLivraison || ""}
                  onChange={handleChange}
                  required
                  placeholder="Adresse de livraison"
                  className="form-control"
                />
              </div>
              <div className="form-group">
                <label>Contact Transporteur</label>
                <input
                  type="text"
                  name="contactTransporteur"
                  value={formData.contactTransporteur || ""}
                  onChange={handleChange}
                  placeholder="Téléphone ou email du transporteur"
                  className="form-control"
                />
              </div>
            </div>

            <div className="form-row" style={{ paddingBottom: "20px" }}>
              <div className="form-group" style={{ flex: "1 1 100%" }}>
                <label>Statut de la livraison</label>
                <select
                  name="statutLivraison"
                  value={formData.statutLivraison || "en cours"}
                  onChange={handleChange}
                  required
                  className="form-control"
                >
                  <option value="en cours">En cours</option>
                  <option value="livrée">Livrée</option>
                </select>
              </div>
            </div>
          </div>

          <div className="modal-actions">
            <button
              type="button"
              onClick={onClose}
              className="btn btn-secondary"
              disabled={loading}
            >
              Annuler
            </button>
            <button
              type="submit"
              className="btn btn-primary"
              disabled={loading}
              style={{ display: "flex", alignItems: "center", gap: "8px" }}
            >
              {loading ? (
                <>
                  <div className="loading-spinner-small"></div>
                  Enregistrement...
                </>
              ) : (
                <>
                  <FaCheckCircle /> Enregistrer
                </>
              )}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default LivraisonModal;