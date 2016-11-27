SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


INSERT INTO `Capability` (`ResourceId`, `Capability`) VALUES
(32, 'Chain'),
(32, 'Hook'),
(33, 'Defibrillator'),
(33, 'Stretcher'),
(34, 'Chain'),
(34, 'Hook'),
(34, 'Trailer'),
(35, 'Batter pack'),
(36, '500 ton capacity');

INSERT INTO `Company` (`UserName`, `Headquarters`) VALUES
('peach', 'Atlanta');

INSERT INTO `CostUnit` (`CostUnitId`, `CostUnit`) VALUES
(2, 'Day'),
(4, 'Each'),
(1, 'Hour'),
(3, 'Week');

INSERT INTO `EmergencySupportFunctions` (`ESFId`, `ESFName`) VALUES
(1, 'Transportation'),
(2, 'Communications'),
(3, 'Public Works and Engineering'),
(4, 'Firefighting'),
(5, 'Emergency Management'),
(6, 'Mass Care, Emergency Assistance, Housing, and Human Services'),
(7, 'Logistics Management and Resource Support'),
(8, 'Public Health and Medical Services'),
(9, 'Search and Rescue'),
(10, 'Oil and Hazardous Materials Response'),
(11, 'Agriculture and Natural Resources'),
(12, 'Energy'),
(13, 'Public Safety and Security'),
(14, 'Long-Term Community Recovery'),
(15, 'External Affairs');

INSERT INTO `GovernmentAgency` (`UserName`, `Jurisdiction`) VALUES
('fulton', 'State');

INSERT INTO `Incidents` (`IncidentId`, `IncidentDate`, `Description`, `IncidentOwner`, `Latitude`, `Longitude`) VALUES
(15, '2016-11-27', 'Fire at Spruill Oaks Library', 'coa', '34.014125', '-84.224507'),
(16, '2016-11-27', 'Traffic Accident', 'coa', '34.014125', '-84.224507'),
(17, '2016-11-27', 'Car Breakdown on highway', 'peach', '34.047765', '-84.272489'),
(18, '2016-11-27', 'Lost person', 'peach', '34.051861', '-84.270847'),
(19, '2016-11-27', 'Mud Slide', 'coa', '34.051450', '-84.271974');

INSERT INTO `Individual` (`UserName`, `JobTitle`, `HireDate`) VALUES
('john', 'Contractor', '2016-11-01');

INSERT INTO `Municipality` (`UserName`, `Population`) VALUES
('coa', 32212);

INSERT INTO `Resources` (`ResourceId`, `ResourceName`, `Model`, `PrimaryESFId`, `Status`, `ResourceOwner`, `Latitude`, `Longitude`, `CostAmount`, `CostUnitId`) VALUES
(30, 'Fire Engine', '2015 model', 4, 'Available', 'coa', '34.023495', '-84.209973', '150.00', 1),
(32, 'Towing Truck', 'Caterpillar 2016 model', 1, 'In Use', 'coa', '34.024900', '-84.207302', '300.50', 1),
(33, 'Ambulance', '', 5, 'In Repair', 'coa', '34.024900', '-84.207302', '30.00', 4),
(34, 'Towing Truck', 'Caterpillar 2016 model', 1, 'Available', 'peach', '34.046254', '-84.267832', '150.75', 1),
(35, 'Walkie Talkie', 'Motorola 2012', 2, 'Available', 'peach', '34.047845', '-84.270182', '2.20', 3),
(36, 'Dump truck', 'Caterpillar 2015', 1, 'Available', 'coa', '34.050710', '-84.272902', '200.52', 1);

INSERT INTO `Resource_AdditionalESF` (`ResourceId`, `AdditionalESFId`) VALUES
(33, 1),
(31, 3),
(32, 3),
(34, 3),
(36, 3),
(32, 5),
(34, 5),
(35, 5),
(36, 5),
(33, 6),
(33, 8);

INSERT INTO `USERS` (`UserName`, `Name`, `Password`, `UserType`) VALUES
('coa', 'City Of Atlanta', 'coa', 'Municipality'),
('fulton', 'Fulton County Emergency ', 'fulton', 'GovernmentAgency'),
('john', 'John Doe', 'john', 'Individual'),
('peach', 'Peachtree Company LLC', 'xyz', 'Company');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
