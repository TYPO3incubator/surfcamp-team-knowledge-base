CREATE TABLE tx_knowledgebase_domain_model_document (
    headline varchar(255) NOT NULL DEFAULT '',
    markup mediumtext,
    type varchar(20) NOT NULL DEFAULT 'normal',
    visibility varchar(20) NOT NULL DEFAULT 'public',
    parent int(11) unsigned NOT NULL DEFAULT 0,
    user int(11) unsigned NOT NULL DEFAULT 0
);
