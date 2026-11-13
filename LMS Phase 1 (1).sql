CREATE TABLE `tenants` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `owner_name` varchar(150),
  `library_name` varchar(200),
  `email` varchar(150),
  `phone` varchar(20),
  `address` text,
  `status` varchar(30),
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `branches` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `tenant_id` bigint NOT NULL,
  `name` varchar(150),
  `code` varchar(50),
  `address` text,
  `phone` varchar(20),
  `status` varchar(30),
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `plans` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(100),
  `price` decimal(12,2),
  `billing_cycle` varchar(30),
  `max_branches` int,
  `max_students` int,
  `features` json,
  `status` varchar(30),
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `tenant_plans` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `tenant_id` bigint NOT NULL,
  `plan_id` bigint NOT NULL,
  `starts_at` datetime,
  `expires_at` datetime,
  `amount` decimal(12,2),
  `status` varchar(30),
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `users` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `tenant_id` bigint NOT NULL,
  `branch_id` bigint,
  `name` varchar(150),
  `email` varchar(150),
  `phone` varchar(20),
  `password` varchar(255),
  `user_type` varchar(30) COMMENT 'owner, staff, student',
  `status` varchar(30),
  `email_verified_at` timestamp,
  `remember_token` varchar(100),
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `staff` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `user_id` bigint UNIQUE NOT NULL,
  `employee_code` varchar(50),
  `designation` varchar(100),
  `joining_date` date,
  `address` text,
  `status` varchar(30),
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `students` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `user_id` bigint UNIQUE NOT NULL,
  `student_code` varchar(50),
  `admission_date` date,
  `date_of_birth` date,
  `address` text,
  `guardian_name` varchar(150),
  `guardian_phone` varchar(20),
  `status` varchar(30),
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `roles` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `tenant_id` bigint COMMENT 'NULL for system-level roles',
  `name` varchar(100),
  `description` text,
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `permissions` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(150) UNIQUE,
  `module` varchar(100),
  `description` text,
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `staff_roles` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `staff_id` bigint NOT NULL,
  `role_id` bigint NOT NULL,
  `created_at` timestamp
);

CREATE TABLE `role_permissions` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `role_id` bigint NOT NULL,
  `permission_id` bigint NOT NULL,
  `created_at` timestamp
);

CREATE TABLE `tenant_settings` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `tenant_id` bigint NOT NULL,
  `setting_key` varchar(150),
  `setting_value` json,
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `branch_settings` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `branch_id` bigint NOT NULL,
  `setting_key` varchar(150),
  `setting_value` json,
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `halls` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `branch_id` bigint NOT NULL,
  `name` varchar(150),
  `description` text,
  `status` varchar(30),
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `floors` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `branch_id` bigint NOT NULL,
  `hall_id` bigint NOT NULL,
  `name` varchar(150),
  `floor_number` int,
  `width` decimal(10,2),
  `height` decimal(10,2),
  `status` varchar(30),
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `floor_plan_objects` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `floor_id` bigint NOT NULL,
  `type` varchar(50) COMMENT 'WALL, BLOCKER, SEAT, WASHROOM, ENTRY_GATE, RECEPTION, OFFICE, OTHER',
  `title` varchar(150),
  `x` decimal(10,2),
  `y` decimal(10,2),
  `width` decimal(10,2),
  `height` decimal(10,2),
  `rotation` decimal(6,2),
  `properties` json,
  `z_index` int,
  `status` varchar(30),
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `seats` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `floor_plan_object_id` bigint UNIQUE NOT NULL,
  `branch_id` bigint NOT NULL,
  `seat_number` varchar(50),
  `seat_type` varchar(50),
  `status` varchar(30),
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `shifts` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `branch_id` bigint NOT NULL,
  `name` varchar(100),
  `start_time` time,
  `end_time` time,
  `status` varchar(30),
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `fee_structures` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `branch_id` bigint NOT NULL,
  `shift_id` bigint NOT NULL,
  `name` varchar(150),
  `amount` decimal(12,2),
  `duration_type` varchar(30) COMMENT 'daily, weekly, monthly, yearly',
  `status` varchar(30),
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `student_enrollments` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `student_id` bigint NOT NULL,
  `branch_id` bigint NOT NULL,
  `shift_id` bigint NOT NULL,
  `fee_structure_id` bigint NOT NULL,
  `start_date` date,
  `end_date` date,
  `status` varchar(30),
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `student_seat_allocations` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `student_id` bigint NOT NULL,
  `seat_id` bigint NOT NULL,
  `shift_id` bigint NOT NULL,
  `start_date` date,
  `end_date` date,
  `status` varchar(30) COMMENT 'active, completed, cancelled',
  `allocated_by` bigint,
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `student_attendances` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `student_id` bigint NOT NULL,
  `branch_id` bigint NOT NULL,
  `shift_id` bigint NOT NULL,
  `attendance_date` date,
  `check_in` time,
  `check_out` time,
  `status` varchar(30) COMMENT 'present, absent, late, half_day',
  `marked_by` bigint,
  `remarks` text,
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `fee_invoices` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `student_id` bigint NOT NULL,
  `fee_structure_id` bigint,
  `invoice_number` varchar(100) UNIQUE,
  `invoice_date` date,
  `due_date` date,
  `total_amount` decimal(12,2),
  `discount_amount` decimal(12,2),
  `final_amount` decimal(12,2),
  `status` varchar(30) COMMENT 'unpaid, partial, paid, overdue, cancelled',
  `notes` text,
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `fee_transactions` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `invoice_id` bigint NOT NULL,
  `student_id` bigint NOT NULL,
  `amount` decimal(12,2),
  `payment_method` varchar(50),
  `transaction_date` datetime,
  `reference_number` varchar(150),
  `status` varchar(30),
  `received_by` bigint,
  `remarks` text,
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `holidays` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `branch_id` bigint NOT NULL,
  `title` varchar(150),
  `description` text,
  `start_date` date,
  `end_date` date,
  `status` varchar(30),
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `student_documents` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `student_id` bigint NOT NULL,
  `document_type` varchar(100),
  `file_name` varchar(255),
  `file_path` varchar(500),
  `uploaded_by` bigint,
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `notices` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `tenant_id` bigint NOT NULL,
  `branch_id` bigint,
  `title` varchar(200),
  `description` text,
  `audience` varchar(50) COMMENT 'all, students, staff',
  `published_at` datetime,
  `expires_at` datetime,
  `status` varchar(30),
  `created_by` bigint,
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `activity_logs` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `tenant_id` bigint NOT NULL,
  `branch_id` bigint,
  `user_id` bigint,
  `action` varchar(100),
  `module` varchar(100),
  `description` text,
  `ip_address` varchar(45),
  `old_values` json,
  `new_values` json,
  `created_at` timestamp
);

CREATE UNIQUE INDEX `branches_index_0` ON `branches` (`tenant_id`, `code`);

CREATE UNIQUE INDEX `users_index_1` ON `users` (`tenant_id`, `email`);

CREATE UNIQUE INDEX `roles_index_2` ON `roles` (`tenant_id`, `name`);

CREATE UNIQUE INDEX `staff_roles_index_3` ON `staff_roles` (`staff_id`, `role_id`);

CREATE UNIQUE INDEX `role_permissions_index_4` ON `role_permissions` (`role_id`, `permission_id`);

CREATE UNIQUE INDEX `tenant_settings_index_5` ON `tenant_settings` (`tenant_id`, `setting_key`);

CREATE UNIQUE INDEX `branch_settings_index_6` ON `branch_settings` (`branch_id`, `setting_key`);

CREATE UNIQUE INDEX `seats_index_7` ON `seats` (`branch_id`, `seat_number`);

CREATE UNIQUE INDEX `student_attendances_index_8` ON `student_attendances` (`student_id`, `shift_id`, `attendance_date`);

ALTER TABLE `branches` ADD FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`);

ALTER TABLE `tenant_plans` ADD FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`);

ALTER TABLE `tenant_plans` ADD FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`);

ALTER TABLE `tenant_settings` ADD FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`);

ALTER TABLE `users` ADD FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`);

ALTER TABLE `users` ADD FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`);

ALTER TABLE `users` ADD FOREIGN KEY (`id`) REFERENCES `staff` (`user_id`);

ALTER TABLE `users` ADD FOREIGN KEY (`id`) REFERENCES `students` (`user_id`);

ALTER TABLE `roles` ADD FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`);

ALTER TABLE `staff_roles` ADD FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`);

ALTER TABLE `staff_roles` ADD FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

ALTER TABLE `role_permissions` ADD FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

ALTER TABLE `role_permissions` ADD FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`);

ALTER TABLE `branch_settings` ADD FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`);

ALTER TABLE `halls` ADD FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`);

ALTER TABLE `floors` ADD FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`);

ALTER TABLE `floors` ADD FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`);

ALTER TABLE `floor_plan_objects` ADD FOREIGN KEY (`floor_id`) REFERENCES `floors` (`id`);

ALTER TABLE `floor_plan_objects` ADD FOREIGN KEY (`id`) REFERENCES `seats` (`floor_plan_object_id`);

ALTER TABLE `seats` ADD FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`);

ALTER TABLE `shifts` ADD FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`);

ALTER TABLE `fee_structures` ADD FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`);

ALTER TABLE `fee_structures` ADD FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`);

ALTER TABLE `student_enrollments` ADD FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

ALTER TABLE `student_enrollments` ADD FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`);

ALTER TABLE `student_enrollments` ADD FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`);

ALTER TABLE `student_enrollments` ADD FOREIGN KEY (`fee_structure_id`) REFERENCES `fee_structures` (`id`);

ALTER TABLE `student_seat_allocations` ADD FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

ALTER TABLE `student_seat_allocations` ADD FOREIGN KEY (`seat_id`) REFERENCES `seats` (`id`);

ALTER TABLE `student_seat_allocations` ADD FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`);

ALTER TABLE `student_seat_allocations` ADD FOREIGN KEY (`allocated_by`) REFERENCES `users` (`id`);

ALTER TABLE `student_attendances` ADD FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

ALTER TABLE `student_attendances` ADD FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`);

ALTER TABLE `student_attendances` ADD FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`);

ALTER TABLE `student_attendances` ADD FOREIGN KEY (`marked_by`) REFERENCES `users` (`id`);

ALTER TABLE `fee_invoices` ADD FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

ALTER TABLE `fee_invoices` ADD FOREIGN KEY (`fee_structure_id`) REFERENCES `fee_structures` (`id`);

ALTER TABLE `fee_transactions` ADD FOREIGN KEY (`invoice_id`) REFERENCES `fee_invoices` (`id`);

ALTER TABLE `fee_transactions` ADD FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

ALTER TABLE `fee_transactions` ADD FOREIGN KEY (`received_by`) REFERENCES `users` (`id`);

ALTER TABLE `holidays` ADD FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`);

ALTER TABLE `student_documents` ADD FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

ALTER TABLE `student_documents` ADD FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`);

ALTER TABLE `notices` ADD FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`);

ALTER TABLE `notices` ADD FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`);

ALTER TABLE `notices` ADD FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

ALTER TABLE `activity_logs` ADD FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`);

ALTER TABLE `activity_logs` ADD FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`);

ALTER TABLE `activity_logs` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
