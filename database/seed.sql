-- Seed data

INSERT INTO admins (name, email, password_hash, is_active) VALUES
('Admin', 'admin@example.com', '$2y$10$8XcHpYb6Q6PLeqXuerADUeX5v1rby4DJfr4gIqDbtus2e0pPMvpRS', 1)
ON DUPLICATE KEY UPDATE
name = VALUES(name),
password_hash = VALUES(password_hash),
is_active = VALUES(is_active);

INSERT INTO settings (`key`, `value`) VALUES
('site_name','APX'),
('home_hero_subtitle','Clean, modern travel agency website with dynamic bookings.'),
('contact_email','info@apx.com'),
('contact_phone','+94770000000'),
('contact_phone_label','+94 77 000 0000'),
('contact_address','Colombo, Sri Lanka'),
('footer_tagline','Your joyful journey is in our care'),
('social_facebook','#'),
('social_instagram','#'),
('social_youtube','#'),
('social_tiktok','#'),
('whatsapp_number','94770000000'),
('about_subtitle','We help you travel with confidence.'),
('about_text','We provide travel services including visas, flights, hotels, and insurance.')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

INSERT INTO settings (`key`, `value`) VALUES
('theme_enabled','1'),
('theme_switcher_enabled','1'),
('theme_mode','light'),
('clock_enabled','0'),
('clock_time_format','24')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

INSERT INTO destinations (name, slug, country, description, visa_note, is_featured, is_active, sort_order) VALUES
('Dubai','dubai','UAE','A modern city with iconic attractions and world-class shopping.','Tourist visa required for many nationalities. Contact us for the latest checklist.',1,1,1),
('Singapore','singapore','Singapore','A clean, vibrant city-state known for attractions and food.','E-visa may be required depending on nationality.',1,1,2),
('Bangkok','bangkok','Thailand','Culture, street food, and shopping.','Visa on arrival may apply for certain passports.',1,1,3)
ON DUPLICATE KEY UPDATE slug = slug;

INSERT INTO flights (title, summary, origin, destination, price_from, is_deal, is_active) VALUES
('Colombo to Dubai','Limited-time fares for selected dates.','CMB','DXB','$299',1,1),
('Colombo to Singapore','Best-value options with flexible dates.','CMB','SIN','$259',1,1)
ON DUPLICATE KEY UPDATE title = title;

INSERT INTO hotels (name, city, country, price_from, is_featured, is_active) VALUES
('Marina Bay Stay','Singapore','Singapore','$120/night',1,1),
('Downtown Comfort','Dubai','UAE','$110/night',1,1)
ON DUPLICATE KEY UPDATE name = name;

INSERT INTO insurance_packages (name, summary, coverage_text, price_from, sort_order, is_active) VALUES
('Basic Cover','Essential medical + trip assistance.','Medical emergencies, trip assistance, baggage cover.','$19',1,1),
('Premium Cover','Higher coverage with add-ons.','Higher medical coverage, cancellation, delays.','$39',2,1)
ON DUPLICATE KEY UPDATE name = name;

INSERT INTO visas (destination_id, title, summary, requirements, processing_days, fee_from, is_active)
SELECT d.id, 'Tourist Visa Assistance', 'Document guidance + submission support.', 'Passport, photos, itinerary, bank statement.', 7, 99.00, 1
FROM destinations d WHERE d.slug = 'dubai'
ON DUPLICATE KEY UPDATE title = title;

INSERT INTO testimonials (customer_name, customer_title, rating, message, is_active) VALUES
('Ayesha','Visa Service',5,'Fast response and very professional support.',1),
('Ravi','Flight Booking',5,'Got a great deal and smooth booking process.',1)
ON DUPLICATE KEY UPDATE customer_name = customer_name;

INSERT INTO blog_posts (author_admin_id, title, slug, excerpt, content, status, published_at)
SELECT a.id, 'Top 5 Travel Tips for 2026', 'top-5-travel-tips-2026', 'Practical tips to save money and travel smarter.', '1) Book early\n2) Keep documents ready\n3) Compare deals\n4) Buy insurance\n5) Check visa rules', 'published', NOW()
FROM admins a WHERE a.email = 'admin@example.com'
ON DUPLICATE KEY UPDATE slug = slug;

INSERT INTO services (icon, title, description, link, sort_order, is_active) VALUES
('✈️','Flight Ticket','Find the best fares and routes for your next trip.','/flights',1,1),
('🛂','Visa Services','End-to-end visa assistance with expert guidance.','/visas',2,1),
('💳','Finance','Flexible solutions to support your travel plans.','/flights#finance',3,1),
('🛡️','Insurance','Travel insurance for a safer journey.','/insurance',4,1),
('🏨','Hotel Booking','Comfortable stays curated for your destination.','/hotels',5,1)
ON DUPLICATE KEY UPDATE title = title;

INSERT INTO pages (`key`, title, slug, content, is_active)
VALUES
('about','About','about','We provide travel services including visas, flights, hotels, and insurance.',1),
('flights','Flight Ticket','flights','Ticketing, routing, and best fare support.',1),
('visas','Visa Services','visas','Visa assistance and application guidance.',1),
('finance','Finance','finance','Flexible travel finance solutions.',1),
('insurance','Insurance','insurance','Travel insurance for a safer journey.',1),
('hotels','Hotel','hotels','Comfortable stays curated for your destination.',1)
ON DUPLICATE KEY UPDATE title = VALUES(title);

INSERT INTO posts (author_admin_id, title, slug, content, status, publish_date)
SELECT a.id, 'Seasonal offers', 'seasonal-offers', 'Limited-time flight deals.', 'published', CURDATE()
FROM admins a WHERE a.email = 'admin@example.com'
ON DUPLICATE KEY UPDATE slug = slug;

INSERT INTO posts (author_admin_id, title, slug, content, status, publish_date)
SELECT a.id, 'Visa updates', 'visa-updates', 'Guidance and checklists.', 'published', CURDATE()
FROM admins a WHERE a.email = 'admin@example.com'
ON DUPLICATE KEY UPDATE slug = slug;

INSERT INTO posts (author_admin_id, title, slug, content, status, publish_date)
SELECT a.id, 'Hotel picks', 'hotel-picks', 'Best stays for your budget.', 'published', CURDATE()
FROM admins a WHERE a.email = 'admin@example.com'
ON DUPLICATE KEY UPDATE slug = slug;

-- Admin login: admin@example.com / password: admin123
