-- ===========================================================================
-- TalentMatch — Catálogo de vagas fictícias (fallback de IA)
-- ===========================================================================
-- Estas vagas são usadas quando a DeepSeek API está indisponível ou sem saldo.
-- Todas as empresas, cargos e salários são FICTÍCIOS e servem apenas para
-- fins educacionais. Nunca representam oportunidades reais.
--
-- Instalação:
--   mysql -u USUARIO -p NOME_BANCO < database/seed_jobs.sql
-- ===========================================================================

SET NAMES utf8mb4;

TRUNCATE TABLE `job_catalog`;

INSERT INTO `job_catalog`
(`area`, `job_title`, `company_name`, `company_sector`, `opportunity_type`, `salary_range`,
 `weekly_hours`, `work_mode`, `contract_type`, `benefits`, `activities`,
 `required_skills`, `desirable_skills`, `related_experience`, `relevant_education`,
 `description`)
VALUES

-- 1) Desenvolvimento de software
('software', 'Desenvolvedor Web Júnior', 'NovaLógica Sistemas', 'Tecnologia da Informação',
 'Júnior', 'R$ 2.800 – R$ 3.600 (ilustrativo)', '40h semanais', 'Híbrido', 'CLT',
 '["Vale-transporte","Plano de saúde fictício","Day off de aniversário","Auxílio home office"]',
 '["Desenvolver e manter aplicações web","Integrar interfaces com APIs REST","Escrever consultas em banco de dados","Participar de revisões de código"]',
 '["HTML","CSS","JavaScript","PHP","MySQL"]',
 '["Git","Bootstrap","APIs REST","Inglês técnico"]',
 'Projetos pessoais, acadêmicos ou freelances com desenvolvimento web.',
 'Curso técnico, tecnólogo ou graduação em andamento em Computação ou área correlata.',
 'Posição de entrada para quem está construindo carreira em desenvolvimento web, com apoio de mentoria e code review.'),

-- 2) Estágio em desenvolvimento
('software', 'Estagiário de Desenvolvimento', 'Laboratório Aurora Tech', 'Software e Serviços',
 'Estágio', 'R$ 1.400 – R$ 1.800 (ilustrativo)', '30h semanais', 'Remoto', 'Estágio',
 '["Bolsa-auxílio","Vale-transporte","Horário flexível","Trilha de aprendizado"]',
 '["Apoiar o desenvolvimento de funcionalidades simples","Escrever e executar testes básicos","Documentar rotinas técnicas","Aprender boas práticas de versionamento"]',
 '["Lógica de programação","HTML","JavaScript"]',
 '["Python","Git","SQL"]',
 'Projetos acadêmicos, iniciação científica ou participação em cursos práticos.',
 'Cursando ensino técnico ou graduação em Computação, Sistemas ou afins.',
 'Oportunidade de aprendizado supervisionado para estudantes que desejam iniciar na área de tecnologia.'),

-- 3) Desenvolvedor Python
('software', 'Desenvolvedor Python Júnior', 'DataForge Analytics', 'Análise de Dados',
 'Júnior', 'R$ 3.200 – R$ 4.200 (ilustrativo)', '40h semanais', 'Remoto', 'PJ',
 '["Auxílio home office","Plano de saúde fictício","Gympass fictício","Bônus por entrega"]',
 '["Automatizar tarefas com scripts Python","Criar e consumir APIs","Tratar e organizar dados","Colaborar com a equipe de dados"]',
 '["Python","Lógica de programação","SQL"]',
 '["Pandas","Flask","Git","Docker"]',
 'Experiência com automação, scripts, projetos acadêmicos ou pessoais em Python.',
 'Tecnólogo ou graduação em Computação, Engenharia ou área quantitativa.',
 'Vaga para desenvolvedores iniciantes com foco em automação e integração de dados.'),

-- 4) Front-end
('software', 'Desenvolvedor Front-end Júnior', 'Pixel & Código Studio', 'Agência Digital',
 'Júnior', 'R$ 2.600 – R$ 3.400 (ilustrativo)', '40h semanais', 'Híbrido', 'CLT',
 '["Vale-refeição","Plano de saúde fictício","Ambiente descontraído","Workshops internos"]',
 '["Construir interfaces responsivas","Implementar layouts a partir de protótipos","Consumir APIs com JavaScript","Cuidar de acessibilidade básica"]',
 '["HTML","CSS","JavaScript"]',
 '["Figma","Bootstrap","React","Testes de usabilidade"]',
 'Projetos de portfólio, trabalhos acadêmicos ou freelances com interfaces web.',
 'Curso técnico, tecnólogo ou graduação em andamento em área relacionada a web/design.',
 'Posição focada em experiência do usuário e interfaces acessíveis e responsivas.'),

-- 5) Análise de dados
('dados', 'Analista de Dados Júnior', 'Insight Vale Consultoria', 'Consultoria e Dados',
 'Júnior', 'R$ 3.000 – R$ 4.000 (ilustrativo)', '40h semanais', 'Híbrido', 'CLT',
 '["Vale-transporte","Plano de saúde fictício","Auxílio educação","Plano de carreira"]',
 '["Construir relatórios e dashboards","Tratar bases de dados","Escrever consultas SQL","Apresentar resultados para a equipe"]',
 '["SQL","Excel","Análise de dados"]',
 '["Python","Power BI","Estatística básica","Comunicação"]',
 'Projetos acadêmicos ou pessoais com análise de dados e relatórios.',
 'Graduação ou tecnólogo em Dados, Estatística, Computação, Administração ou afins.',
 'Vaga para quem gosta de transformar dados em decisões e relatórios claros.'),

-- 6) Estágio em dados
('dados', 'Estagiário de Dados', 'Insight Vale Consultoria', 'Consultoria e Dados',
 'Estágio', 'R$ 1.300 – R$ 1.700 (ilustrativo)', '30h semanais', 'Remoto', 'Estágio',
 '["Bolsa-auxílio","Auxílio home office","Mentoria","Cursos gratuitos"]',
 '["Auxiliar na organização de planilhas","Apoiar a criação de relatórios","Realizar consultas simples em SQL","Documentar processos"]',
 '["Excel","Lógica de programação"]',
 '["SQL","Python","Power BI"]',
 'Atividades acadêmicas com planilhas, estatística ou programação.',
 'Cursando graduação ou técnico em Dados, TI, Estatística ou áreas afins.',
 'Oportunidade de entrada para estudantes interessados em dados e relatórios.'),

-- 7) Suporte técnico
('suporte', 'Suporte Técnico Nível 1', 'ConectaServiços TI', 'Suporte e Infraestrutura',
 'Júnior', 'R$ 2.000 – R$ 2.600 (ilustrativo)', '40h semanais', 'Presencial', 'CLT',
 '["Vale-transporte","Vale-refeição","Plano de saúde fictício","Treinamento inicial"]',
 '["Atender chamados de usuários","Registrar e acompanhar tickets","Realizar manutenção básica de equipamentos","Orientar usuários sobre sistemas"]',
 '["Suporte técnico","Comunicação","Windows"]',
 '["Redes","Hardware","Excel","Inglês técnico"]',
 'Ajudar colegas, familiares ou projetos com manutenção e suporte de computadores.',
 'Curso técnico em Informática, tecnólogo ou graduação em TI (ou em andamento).',
 'Vaga de entrada em suporte, com treinamento e trilha de crescimento.'),

-- 8) Estágio em suporte
('suporte', 'Estagiário de Suporte Técnico', 'ConectaServiços TI', 'Suporte e Infraestrutura',
 'Estágio', 'R$ 1.100 – R$ 1.500 (ilustrativo)', '30h semanais', 'Presencial', 'Estágio',
 '["Bolsa-auxílio","Vale-transporte","Vale-refeição","Certificações custeadas"]',
 '["Auxiliar no atendimento de chamados","Apoiar a manutenção de computadores","Organizar inventário de equipamentos","Aprender rotinas de infraestrutura"]',
 '["Comunicação","Windows","Organização"]',
 '["Redes","Hardware","Linux"]',
 'Interesse por montagem, manutenção e configuração de computadores.',
 'Cursando técnico ou graduação em Informática, TI ou afins.',
 'Porta de entrada para quem quer aprender suporte e infraestrutura na prática.'),

-- 9) Assistente de TI
('ti', 'Assistente de TI', 'Meridiano Corporativo', 'Serviços Corporativos',
 'Júnior', 'R$ 2.200 – R$ 2.900 (ilustrativo)', '40h semanais', 'Híbrido', 'CLT',
 '["Vale-transporte","Plano de saúde fictício","Vale-refeição","Desconto em cursos"]',
 '["Apoiar usuários internos","Configurar estações de trabalho","Controlar acessos e permissões","Manter documentação de TI"]',
 '["Suporte técnico","Windows","Organização"]',
 '["Redes","Excel","Google Workspace","Comunicação"]',
 'Experiência acadêmica ou pessoal com configuração de sistemas e apoio a usuários.',
 'Técnico ou graduação em TI, Sistemas ou Administração com foco em tecnologia.',
 'Vaga generalista de TI para quem gosta de resolver problemas do dia a dia.'),

-- 10) Auxiliar de infraestrutura
('ti', 'Auxiliar de Infraestrutura', 'Nuvem Prisma Cloud', 'Cloud e Infraestrutura',
 'Júnior', 'R$ 2.400 – R$ 3.200 (ilustrativo)', '40h semanais', 'Remoto', 'PJ',
 '["Auxílio home office","Plano de saúde fictício","Certificação em nuvem","Horário flexível"]',
 '["Monitorar serviços básicos","Apoiar a configuração de servidores","Documentar procedimentos","Auxiliar em rotinas de backup"]',
 '["Redes","Linux","Windows"]',
 '["Docker","Cloud","Shell Script","Git"]',
 'Projetos com servidores, redes ou ambientes em nuvem (acadêmicos ou pessoais).',
 'Técnico, tecnólogo ou graduação em TI, Redes ou Sistemas.',
 'Oportunidade para iniciar em infraestrutura e computação em nuvem.'),

-- 11) Design
('design', 'Designer de Interface Júnior', 'Pixel & Código Studio', 'Agência Digital',
 'Júnior', 'R$ 2.500 – R$ 3.300 (ilustrativo)', '40h semanais', 'Remoto', 'PJ',
 '["Auxílio home office","Assinatura de ferramentas","Flexibilidade de horário","Feedback contínuo"]',
 '["Criar wireframes e protótipos","Montar design systems simples","Colaborar com desenvolvedores","Realizar testes de usabilidade básicos"]',
 '["Design","Figma","HTML"]',
 '["CSS","UX","Ilustração","Acessibilidade"]',
 'Portfólio com projetos de interface, acadêmicos ou pessoais.',
 'Curso de Design, UX/UI ou áreas correlatas (ou em andamento).',
 'Vaga para designers iniciantes focados em interfaces digitais.'),

-- 12) Administração
('administracao', 'Assistente Administrativo', 'Meridiano Corporativo', 'Serviços Corporativos',
 'Júnior', 'R$ 1.900 – R$ 2.500 (ilustrativo)', '40h semanais', 'Presencial', 'CLT',
 '["Vale-transporte","Vale-refeição","Plano de saúde fictício","Plano de carreira"]',
 '["Organizar documentos e planilhas","Apoiar rotinas de departamento pessoal","Atender fornecedores e equipes","Controlar prazos e agenda"]',
 '["Excel","Organização","Comunicação"]',
 '["Pacote Office","Gestão de processos","Atendimento"]',
 'Atividades acadêmicas, voluntárias ou profissionais com organização e controle.',
 'Ensino médio completo, técnico ou graduação em Administração (ou em andamento).',
 'Vaga administrativa para quem tem organização e boa comunicação.'),

-- 13) Marketing
('marketing', 'Assistente de Marketing Digital', 'Vitrine Criativa', 'Marketing e Publicidade',
 'Júnior', 'R$ 2.100 – R$ 2.800 (ilustrativo)', '40h semanais', 'Híbrido', 'CLT',
 '["Vale-transporte","Vale-refeição","Plano de saúde fictício","Cursos de marketing"]',
 '["Apoiar a criação de conteúdo para redes sociais","Acompanhar métricas de campanhas","Organizar calendário editorial","Auxiliar na produção de materiais"]',
 '["Comunicação","Marketing digital","Redes sociais"]',
 '["Canva","Excel","Copywriting","Google Analytics"]',
 'Projetos acadêmicos, pessoais ou voluntários com comunicação e redes sociais.',
 'Técnico ou graduação em Marketing, Comunicação, Publicidade ou afins.',
 'Vaga para quem gosta de conteúdo, métricas e comunicação com o público.'),

-- 14) Outras áreas
('outras', 'Assistente de Projetos', 'Órbita Inovação', 'Gestão e Projetos',
 'Júnior', 'R$ 2.300 – R$ 3.000 (ilustrativo)', '40h semanais', 'Híbrido', 'CLT',
 '["Vale-transporte","Plano de saúde fictício","Vale-refeição","Mentoria de carreira"]',
 '["Acompanhar cronogramas de projetos","Organizar reuniões e atas","Apoiar a comunicação entre equipes","Manter documentação atualizada"]',
 '["Organização","Comunicação","Trabalho em equipe"]',
 '["Excel","Metodologias ágeis","Gestão de tempo"]',
 'Participação em projetos acadêmicos, voluntários ou competições.',
 'Ensino médio, técnico ou graduação em qualquer área com foco em gestão.',
 'Vaga generalista para quem gosta de organizar projetos e conectar pessoas.');
