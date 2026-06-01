CREATE TABLE IF NOT EXISTS "migrations"(
  "id" integer primary key autoincrement not null,
  "migration" varchar not null,
  "batch" integer not null
);
CREATE TABLE IF NOT EXISTS "users"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "email" varchar not null,
  "email_verified_at" datetime,
  "password" varchar not null,
  "remember_token" varchar,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "users_email_unique" on "users"("email");
CREATE TABLE IF NOT EXISTS "password_reset_tokens"(
  "email" varchar not null,
  "token" varchar not null,
  "created_at" datetime,
  primary key("email")
);
CREATE TABLE IF NOT EXISTS "sessions"(
  "id" varchar not null,
  "user_id" integer,
  "ip_address" varchar,
  "user_agent" text,
  "payload" text not null,
  "last_activity" integer not null,
  primary key("id")
);
CREATE INDEX "sessions_user_id_index" on "sessions"("user_id");
CREATE INDEX "sessions_last_activity_index" on "sessions"("last_activity");
CREATE TABLE IF NOT EXISTS "cache"(
  "key" varchar not null,
  "value" text not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE TABLE IF NOT EXISTS "cache_locks"(
  "key" varchar not null,
  "owner" varchar not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE TABLE IF NOT EXISTS "jobs"(
  "id" integer primary key autoincrement not null,
  "queue" varchar not null,
  "payload" text not null,
  "attempts" integer not null,
  "reserved_at" integer,
  "available_at" integer not null,
  "created_at" integer not null
);
CREATE INDEX "jobs_queue_index" on "jobs"("queue");
CREATE TABLE IF NOT EXISTS "job_batches"(
  "id" varchar not null,
  "name" varchar not null,
  "total_jobs" integer not null,
  "pending_jobs" integer not null,
  "failed_jobs" integer not null,
  "failed_job_ids" text not null,
  "options" text,
  "cancelled_at" integer,
  "created_at" integer not null,
  "finished_at" integer,
  primary key("id")
);
CREATE TABLE IF NOT EXISTS "failed_jobs"(
  "id" integer primary key autoincrement not null,
  "uuid" varchar not null,
  "connection" text not null,
  "queue" text not null,
  "payload" text not null,
  "exception" text not null,
  "failed_at" datetime not null default CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX "failed_jobs_uuid_unique" on "failed_jobs"("uuid");
CREATE TABLE IF NOT EXISTS "promotions"(
  "numPromotion" integer primary key autoincrement not null,
  "nomPromotion" varchar not null,
  "valeur" numeric not null,
  "dateDebut" datetime,
  "dateFin" datetime,
  "codePromo" varchar,
  "statutPromotion" varchar not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "promotions_codepromo_unique" on "promotions"("codePromo");
CREATE TABLE IF NOT EXISTS "categories"(
  "numCategorie" integer primary key autoincrement not null,
  "nomCategorie" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "produits"(
  "numProduit" integer primary key autoincrement not null,
  "nomProduit" varchar not null,
  "prix" numeric not null,
  "quantiteStock" integer not null default '0',
  "poids" numeric not null,
  "image" varchar not null,
  "numCategorie" integer not null,
  "numPromotion" integer,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("numCategorie") references "categories"("numCategorie") on delete restrict,
  foreign key("numPromotion") references "promotions"("numPromotion") on delete set null
);
CREATE TABLE IF NOT EXISTS "mode_paiements"(
  "numModePaiement" integer primary key autoincrement not null,
  "nomModePaiement" varchar not null,
  "solde" numeric not null default '0',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "utilisateurs"(
  "numUtilisateur" integer primary key autoincrement not null,
  "nomUtilisateur" varchar not null,
  "contact" varchar not null,
  "email" varchar not null,
  "motDePasse" varchar not null,
  "role" varchar check("role" in('admin', 'client')) not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "utilisateurs_email_unique" on "utilisateurs"("email");
CREATE TABLE IF NOT EXISTS "commandes"(
  "numCommande" integer primary key autoincrement not null,
  "numUtilisateur" integer not null,
  "dateCommande" datetime,
  "statut" varchar check("statut" in('en cours', 'récu')) not null default 'en cours',
  "montantTotal" numeric not null default '0',
  "numModePaiement" integer not null,
  "adresseDeLivraison" varchar not null,
  "payerLivraison" tinyint(1) not null default '0',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("numUtilisateur") references "utilisateurs"("numUtilisateur") on delete cascade,
  foreign key("numModePaiement") references "mode_paiements"("numModePaiement") on delete restrict
);
CREATE TABLE IF NOT EXISTS "detail_commandes"(
  "numDetailCommande" integer primary key autoincrement not null,
  "numCommande" integer not null,
  "numProduit" integer not null,
  "poids" numeric not null default '0.25',
  "decoupe" varchar not null default 'entière',
  "prixUnitaire" numeric not null,
  "sousTotal" numeric not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("numCommande") references "commandes"("numCommande") on delete cascade,
  foreign key("numProduit") references "produits"("numProduit") on delete restrict
);
CREATE TABLE IF NOT EXISTS "livraisons"(
  "numLivraison" integer primary key autoincrement not null,
  "numCommande" integer not null,
  "lieuLivraison" varchar,
  "transporteur" varchar not null default 'à déterminer',
  "referenceColis" varchar,
  "fraisLivraison" numeric not null default '0',
  "contactTransporteur" varchar,
  "dateExpedition" datetime,
  "dateLivraison" datetime,
  "statutLivraison" varchar check("statutLivraison" in('en cours', 'en préparation', 'livré(e)s')) not null default 'en préparation',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("numCommande") references "commandes"("numCommande") on delete cascade
);
CREATE TABLE IF NOT EXISTS "paiements"(
  "numPaiement" integer primary key autoincrement not null,
  "numCommande" integer not null,
  "numModePaiement" integer not null,
  "montantApayer" numeric not null,
  "statut" varchar check("statut" in('en attente', 'effectué', 'echoué')) not null,
  "datePaiement" datetime default CURRENT_TIMESTAMP,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("numCommande") references "commandes"("numCommande") on delete cascade,
  foreign key("numModePaiement") references "mode_paiements"("numModePaiement") on delete restrict
);
CREATE TABLE IF NOT EXISTS "paniers"(
  "numPanier" integer primary key autoincrement not null,
  "numUtilisateur" integer not null,
  "dateCreation" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("numUtilisateur") references "utilisateurs"("numUtilisateur") on delete cascade
);
CREATE TABLE IF NOT EXISTS "detail_paniers"(
  "numDetailPanier" integer primary key autoincrement not null,
  "numPanier" integer not null,
  "numProduit" integer not null,
  "poids" numeric not null default '0.25',
  "decoupe" varchar not null default 'entière',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("numPanier") references "paniers"("numPanier") on delete cascade,
  foreign key("numProduit") references "produits"("numProduit") on delete cascade
);
CREATE UNIQUE INDEX "detail_paniers_numpanier_numproduit_unique" on "detail_paniers"(
  "numPanier",
  "numProduit"
);
CREATE TABLE IF NOT EXISTS "articles"(
  "numArticle" integer primary key autoincrement not null,
  "titre" varchar not null,
  "contenu" text not null,
  "description" varchar not null,
  "image" varchar not null,
  "auteur" varchar,
  "datePublication" datetime,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "personal_access_tokens"(
  "id" integer primary key autoincrement not null,
  "tokenable_type" varchar not null,
  "tokenable_id" integer not null,
  "name" text not null,
  "token" varchar not null,
  "abilities" text,
  "last_used_at" datetime,
  "expires_at" datetime,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "personal_access_tokens_tokenable_type_tokenable_id_index" on "personal_access_tokens"(
  "tokenable_type",
  "tokenable_id"
);
CREATE UNIQUE INDEX "personal_access_tokens_token_unique" on "personal_access_tokens"(
  "token"
);
CREATE INDEX "personal_access_tokens_expires_at_index" on "personal_access_tokens"(
  "expires_at"
);
CREATE TABLE IF NOT EXISTS "frais_livraisons"(
  "numFrais" integer primary key autoincrement not null,
  "poidsMin" numeric not null,
  "poidsMax" numeric not null,
  "frais" numeric not null,
  "created_at" datetime,
  "updated_at" datetime
);

INSERT INTO migrations VALUES(1,'0001_01_01_000000_create_users_table',1);
INSERT INTO migrations VALUES(2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO migrations VALUES(3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO migrations VALUES(4,'2025_10_01_084200_create_promotions_table',1);
INSERT INTO migrations VALUES(5,'2025_10_01_084230_create_categories_table',1);
INSERT INTO migrations VALUES(6,'2025_10_01_084231_create_produits_table',1);
INSERT INTO migrations VALUES(7,'2025_10_02_065132_create_mode_paiements_table',1);
INSERT INTO migrations VALUES(8,'2025_10_02_065213_create_utilisateurs_table',1);
INSERT INTO migrations VALUES(9,'2025_10_02_065246_create_commandes_table',1);
INSERT INTO migrations VALUES(10,'2025_10_02_065312_create_detail_commandes_table',1);
INSERT INTO migrations VALUES(11,'2025_10_02_065346_create_livraisons_table',1);
INSERT INTO migrations VALUES(12,'2025_10_02_065357_create_paiements_table',1);
INSERT INTO migrations VALUES(13,'2025_10_02_065407_create_paniers_table',1);
INSERT INTO migrations VALUES(14,'2025_10_02_080430_create_detail_paniers_table',1);
INSERT INTO migrations VALUES(15,'2025_10_02_081921_create_articles_table',1);
INSERT INTO migrations VALUES(16,'2025_10_05_041453_create_personal_access_tokens_table',1);
INSERT INTO migrations VALUES(17,'2025_10_10_072537_create_frais_livraisons_table',1);
