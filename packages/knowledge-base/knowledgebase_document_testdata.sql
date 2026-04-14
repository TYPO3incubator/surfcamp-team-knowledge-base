-- TYPO3 Knowledge Base test data
-- Assumptions:
-- 1) Document table name: tx_knowledgebase_domain_model_document
-- 2) Status table name:   tx_knowledgebase_domain_model_status
-- 3) be_users uid=1 exists (usually the admin backend user).
-- 4) Adjust pid values if your storage folder differs.

START TRANSACTION;

-- Optional status seed data
INSERT INTO `tx_knowledgebase_domain_model_status` (`uid`, `pid`, `tstamp`, `crdate`, `hidden`, `deleted`, `title`, `document`) VALUES
  (1, 1, 1712966401, 1712966401, 0, 0, 'Draft', 1),
  (2, 1, 1712966402, 1712966402, 0, 0, 'Review', 1),
  (3, 1, 1712966403, 1712966403, 0, 0, 'Published', 1),
  (4, 1, 1712966404, 1712966404, 0, 0, 'Archived', 1)
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

-- Document seed data
INSERT INTO `tx_knowledgebase_domain_model_document`
(`uid`, `pid`, `tstamp`, `crdate`, `hidden`, `deleted`, `headline`, `markup`, `type`, `visibility`, `parent`, `status`, `user`) VALUES
  (1111, 1, 1712966401, 1712966401, 0, 0, 'Knowledge Base', 'Top-level collection for all internal documentation.', 'board', 'public', 0, 1, 1),
  (2222, 1, 1712966402, 1712966402, 0, 0, 'Getting Started', 'Board for onboarding and first steps with the platform.', 'board', 'public', 1, 1, 1),
  (3333, 1, 1712966403, 1712966403, 0, 0, 'Editorial Workflows', 'Board for editor-related documentation and publishing guidelines.', 'board', 'public', 1, 1, 1),
  (4444, 1, 1712966404, 1712966404, 0, 0, 'Development', 'Board for extension development, deployment, and coding standards.', 'board', 'public', 1, 1, 1),
  (5555, 1, 1712966405, 1712966405, 0, 0, 'Infrastructure', 'Board for hosting, cache, CLI, and operations topics.', 'board', 'private', 1, 1, 1),
  (6, 1, 1712966406, 1712966406, 0, 0, 'Security', 'Board for permissions, review processes, and secure configuration.', 'board', 'private', 1, 1, 1),
  (7, 1, 1712966407, 1712966407, 0, 0, 'FAQ', 'Board collecting frequent questions and short answers.', 'board', 'public', 1, 1, 1),
  (8, 1, 1712966408, 1712966408, 0, 0, 'How to log in to the TYPO3 backend', 'Use your corporate single sign-on if available. If your account is local, enter username and password on the backend login screen. Contact an administrator if your account is locked.', 'normal', 'public', 2, 2, 1),
  (9, 1, 1712966409, 1712966409, 0, 0, 'How to reset your backend password', 'Open the password reset workflow from the TYPO3 backend login. If password reset is disabled in this environment, ask an administrator to send a reset link.', 'normal', 'private', 2, 2, 1),
  (10, 1, 1712966410, 1712966410, 0, 0, 'Create your first content element', 'Open a page, switch to Page module, create a new content element, select the desired type, enter headline and body text, and save.', 'normal', 'public', 2, 2, 1),
  (11, 1, 1712966411, 1712966411, 0, 0, 'How workspaces are used', 'Draft changes should be created in the editorial workspace. Editors review records before publishing to live. Never edit live directly unless your process explicitly allows it.', 'normal', 'public', 3, 1, 1),
  (12, 1, 1712966412, 1712966412, 0, 0, 'Publish a page to live', 'After review, use the workspace module to submit or publish the prepared changes. Verify teaser text, metadata, and visibility before releasing.', 'normal', 'public', 3, 1, 1),
  (13, 1, 1712966413, 1712966413, 0, 0, 'Naming conventions for pages', 'Use short, descriptive titles. Avoid duplicate page names on the same level. For landing pages, include the product or campaign name first.', 'normal', 'public', 3, 2, 1),
  (14, 1, 1712966414, 1712966414, 0, 0, 'Rich text formatting rules', 'Use headings in correct order, keep paragraphs short, avoid inline styling, and use lists only when they improve scanning.', 'normal', 'public', 3, 2, 1),
  (15, 1, 1712966415, 1712966415, 0, 0, 'When to use categories', 'Categories should be used for thematic grouping, filtering, and automatic content aggregation. Do not use them as a substitute for page hierarchy.', 'normal', 'public', 3, 3, 1),
  (16, 1, 1712966416, 1712966416, 0, 0, 'Extension setup overview', 'Install the extension via Composer, run database schema updates, clear caches, and verify that the site set and TypoScript configuration are loaded.', 'normal', 'private', 4, 1, 1),
  (17, 1, 1712966417, 1712966417, 0, 0, 'Domain models and repositories', 'Keep models lean, use typed properties, and place query logic in repositories. Avoid business logic in controllers where possible.', 'normal', 'private', 4, 2, 1),
  (18, 1, 1712966418, 1712966418, 0, 0, 'Controller action guidelines', 'Controller actions should remain thin. Validate input early, delegate domain logic to services, and keep responses predictable.', 'normal', 'private', 4, 2, 1),
  (19, 1, 1712966419, 1712966419, 0, 0, 'Fluid template structure', 'Store templates, partials, and layouts in separate folders. Prefer descriptive names and keep large components split into partials.', 'normal', 'public', 4, 3, 1),
  (20, 1, 1712966420, 1712966420, 0, 0, 'Database migrations checklist', 'Before deployment, compare schema, verify new indexes, back up production, and document destructive changes in the release notes.', 'normal', 'private', 4, 1, 1),
  (21, 1, 1712966421, 1712966421, 0, 0, 'Scheduler tasks for maintenance', 'Use scheduler tasks for recurring cleanup, indexing, and notification jobs. Monitor runtime and failure frequency after each release.', 'normal', 'private', 5, 2, 1),
  (22, 1, 1712966422, 1712966422, 0, 0, 'Cache clearing strategy', 'Clear only required caches during debugging. In production, prefer targeted cache flushing to avoid unnecessary load spikes.', 'normal', 'private', 5, 2, 1),
  (23, 1, 1712966423, 1712966423, 0, 0, 'DDEV local setup', 'Clone the repository, start ddev, run composer install, import a database, and execute extension setup commands defined in the project README.', 'normal', 'private', 5, 1, 1),
  (24, 1, 1712966424, 1712966424, 0, 0, 'Handling .env values', 'Store environment-specific secrets outside version control. Document required keys and provide safe defaults for local development.', 'normal', 'private', 5, 3, 1),
  (25, 1, 1712966425, 1712966425, 0, 0, 'File storage structure', 'Separate editor uploads, generated assets, and system-managed files. Do not manually rename processed files in storage.', 'normal', 'private', 5, 3, 1),
  (26, 1, 1712966426, 1712966426, 0, 0, 'User roles and permissions', 'Grant the smallest practical set of backend permissions. Review access quarterly and remove obsolete user groups promptly.', 'normal', 'private', 6, 1, 1),
  (27, 1, 1712966427, 1712966427, 0, 0, 'Handling private documents', 'Private knowledge base articles must not expose credentials, personal data, or contract details. Use restricted visibility where needed.', 'normal', 'private', 6, 2, 1),
  (28, 1, 1712966428, 1712966428, 0, 0, 'Security review before release', 'Each release should include a review of backend module access, route exposure, CSRF protections, and composer dependency advisories.', 'normal', 'private', 6, 1, 1),
  (29, 1, 1712966429, 1712966429, 0, 0, 'How to report a vulnerability', 'Report security issues internally first. Include reproduction steps, affected versions, and a preliminary impact assessment.', 'normal', 'private', 6, 3, 1),
  (30, 1, 1712966430, 1712966430, 0, 0, 'Where to find the backend user list', 'Open the system records area or the dedicated backend user administration module depending on project setup.', 'normal', 'public', 7, 2, 1),
  (31, 1, 1712966431, 1712966431, 0, 0, 'Difference between hidden and deleted', 'Hidden records stay in the database and can be restored quickly. Deleted records are marked deleted and should not appear in normal listings.', 'normal', 'public', 7, 2, 1),
  (32, 1, 1712966432, 1712966432, 0, 0, 'How page slugs are generated', 'Page slugs are derived from routing settings and page titles. Check site configuration when duplicate or invalid slugs appear.', 'normal', 'public', 7, 3, 1),
  (33, 1, 1712966433, 1712966433, 0, 0, 'What to do after a deployment', 'Check the install tool warnings, flush caches if required, confirm the scheduler is healthy, and test critical frontend pages.', 'normal', 'public', 7, 1, 1),
  (34, 1, 1712966434, 1712966434, 0, 0, 'How to preview unpublished content', 'Use workspace preview or preview links depending on site configuration. Never send raw backend links to external reviewers.', 'normal', 'public', 7, 2, 1),
  (35, 1, 1712966435, 1712966435, 0, 0, 'Troubleshooting 404 errors', 'Verify page visibility, route enhancers, site base configuration, and web server rewrite rules. Also inspect generated slugs.', 'normal', 'public', 7, 1, 1),
  (36, 1, 1712966436, 1712966436, 0, 0, 'Troubleshooting backend login loops', 'Check cookies, trusted hosts, session storage, reverse proxy headers, and browser extensions that may strip required data.', 'normal', 'private', 7, 1, 1),
  (37, 1, 1712966437, 1712966437, 0, 0, 'Editorial checklist for news articles', 'Confirm headline, teaser, publish date, category mapping, social image, alt texts, and internal links before publication.', 'normal', 'public', 3, 2, 1),
  (38, 1, 1712966438, 1712966438, 0, 0, 'SEO basics for editors', 'Provide a meaningful page title, concise meta description, one primary H1, and descriptive link text. Avoid duplicated metadata.', 'normal', 'public', 3, 3, 1),
  (39, 1, 1712966439, 1712966439, 0, 0, 'Image optimization guidelines', 'Upload appropriately sized images, prefer modern formats where supported, and provide alt text that matches editorial context.', 'normal', 'public', 3, 2, 1),
  (40, 1, 1712966440, 1712966440, 0, 0, 'Release note template', 'Each release note should contain scope, changed areas, risks, migration steps, rollback considerations, and validation results.', 'normal', 'private', 4, 1, 1),
  (41, 1, 1712966441, 1712966441, 0, 0, 'Branching strategy', 'Use short-lived feature branches, protected main branches, and tagged releases. Rebase or merge according to team conventions.', 'normal', 'private', 4, 2, 1),
  (42, 1, 1712966442, 1712966442, 0, 0, 'Composer update policy', 'Minor updates may be batched regularly after validation. Security fixes should be prioritized and documented immediately.', 'normal', 'private', 4, 1, 1),
  (43, 1, 1712966443, 1712966443, 0, 0, 'Logging and monitoring', 'Critical jobs and integrations must log structured messages. Review recurring warnings weekly to catch regressions early.', 'normal', 'private', 5, 2, 1),
  (44, 1, 1712966444, 1712966444, 0, 0, 'Backup and restore process', 'Nightly backups should be verified by scheduled restore tests. Keep restoration steps documented and versioned.', 'normal', 'private', 5, 1, 1),
  (45, 1, 1712966445, 1712966445, 0, 0, 'Incident response basics', 'When production issues occur, record timeline, impact, mitigation steps, and recovery validation before closing the incident.', 'normal', 'private', 5, 1, 1),
  (46, 1, 1712966446, 1712966446, 0, 0, 'Permissions matrix', 'Map each backend role to allowed modules, record tables, file mounts, and workspace actions. Keep the matrix under version control.', 'normal', 'private', 6, 2, 1),
  (47, 1, 1712966447, 1712966447, 0, 0, 'Data protection note', 'Do not store personal data in free text documentation unless explicitly justified and approved by policy.', 'normal', 'private', 6, 3, 1),
  (48, 1, 1712966448, 1712966448, 0, 0, 'Page tree conventions', 'Use a predictable page tree grouped by business domain, not by individual editor preference.', 'normal', 'public', 3, 2, 1),
  (49, 1, 1712966449, 1712966449, 0, 0, 'Content staging process', 'Prepare changes in staging, validate with editors, and only then promote to production through the approved pipeline.', 'normal', 'private', 4, 1, 1),
  (50, 1, 1712966450, 1712966450, 0, 0, 'Search indexing overview', 'The knowledge base search should index only visible documents and respect access restrictions in frontend rendering.', 'normal', 'private', 4, 2, 1),
  (51, 1, 1712966451, 1712966451, 0, 0, 'Document visibility rules', 'Public documents may be shown to all authorized viewers of the knowledge base. Private documents require stricter access evaluation.', 'normal', 'private', 6, 1, 1),
  (52, 1, 1712966452, 1712966452, 0, 0, 'Board usage recommendations', 'Boards should group related documents by process area, not by temporary initiatives unless there is a strong reason.', 'normal', 'public', 1, 3, 1),
  (53, 1, 1712966453, 1712966453, 0, 0, 'Status workflow explanation', 'Draft, review, published, and archived statuses help communicate maturity. Use archived for outdated but still relevant references.', 'normal', 'public', 1, 2, 1),
  (54, 1, 1712966454, 1712966454, 0, 0, 'How parent documents are used', 'Parent references allow hierarchical navigation and breadcrumb generation. Root boards usually have no parent.', 'normal', 'public', 1, 2, 1),
  (55, 1, 1712966455, 1712966455, 0, 0, 'Frontend rendering notes', 'Render markup safely, sanitize rich content as required, and avoid exposing private metadata in frontend templates.', 'normal', 'private', 4, 1, 1),
  (56, 1, 1712966456, 1712966456, 0, 0, 'Accessibility checklist', 'Verify heading order, keyboard access, link purpose, alternative text, and sufficient contrast for all editorial outputs.', 'normal', 'public', 3, 2, 1);

COMMIT;