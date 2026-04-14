CREATE TABLE tx_knowledgebase_domain_model_document (
    FULLTEXT INDEX idx_search (headline, markup)
);

CREATE TABLE tx_knowledgebase_domain_model_embedding (
    uid          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    document     INT UNSIGNED NOT NULL DEFAULT 0,
    vector       LONGTEXT     NOT NULL,
    content_hash VARCHAR(32)  NOT NULL DEFAULT '',
    tstamp       INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (uid),
    UNIQUE KEY uq_document (document)
);
