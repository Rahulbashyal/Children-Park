-- Create the montessori database
CREATE DATABASE IF NOT EXISTS `montessori`;
USE `montessori`;

-- Table structure for table `users`
CREATE TABLE `users` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','teacher') NOT NULL DEFAULT 'teacher',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `events`
CREATE TABLE `events` (
  `eid` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `date` date DEFAULT NULL,
  `image_path` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`eid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `notifications`
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `status` enum('read','unread') NOT NULL DEFAULT 'unread',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`uid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `gallery`
CREATE TABLE `gallery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(500) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `popup_ad`
CREATE TABLE `popup_ad` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `image_path` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample data for users
INSERT INTO `users` (`username`, `password`, `role`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('teacher1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('teacher2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher');

-- Insert sample data for events
INSERT INTO `events` (`title`, `description`, `date`, `image_path`) VALUES
('Annual Sports Day', 'Join us for our annual sports day celebration with various activities and competitions for all students.', '2024-02-15', NULL),
('Parent-Teacher Meeting', 'Monthly parent-teacher meeting to discuss student progress and upcoming activities.', '2024-02-20', NULL),
('Art Exhibition', 'Student art exhibition showcasing creative works from all classes.', '2024-02-25', NULL),
('Science Fair', 'Interactive science fair with experiments and demonstrations by students.', '2024-03-01', NULL),
('Cultural Program', 'Annual cultural program featuring dance, music, and drama performances.', '2024-03-10', NULL);

-- Insert sample data for notifications
INSERT INTO `notifications` (`user_id`, `message`, `status`, `is_read`) VALUES
(2, 'New Notice Added: Annual Sports Day', 'unread', 0),
(3, 'New Notice Added: Annual Sports Day', 'unread', 0),
(2, 'New Notice Added: Parent-Teacher Meeting', 'unread', 0),
(3, 'New Notice Added: Parent-Teacher Meeting', 'unread', 0);

-- Insert sample data for gallery
INSERT INTO `gallery` (`title`, `description`, `image_url`) VALUES
('Classroom Activities', 'Students engaged in various classroom learning activities', 'uploads/gallery/classroom1.jpg'),
('Playground Fun', 'Children enjoying outdoor activities and games', 'uploads/gallery/playground1.jpg'),
('Art Class', 'Creative art sessions with colorful paintings and crafts', 'uploads/gallery/art1.jpg'),
('Science Lab', 'Students conducting experiments in our well-equipped science lab', 'uploads/gallery/science1.jpg'),
('Library Time', 'Quiet reading sessions in our well-stocked library', 'uploads/gallery/library1.jpg');

-- Insert default popup ad entry
INSERT INTO `popup_ad` (`id`, `image_path`) VALUES
(1, 'uploads/default_popup.jpg');

-- Create necessary directories (Note: This is just for reference, directories need to be created manually)
-- uploads/
-- uploads/gallery/
-- uploads/news_images/