-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 23, 2016 at 09:43 PM
-- Server version: 5.7.16
-- PHP Version: 5.6.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `erms`
--

-- --------------------------------------------------------

--
-- Table structure for table `Capability`
--

DROP TABLE IF EXISTS `Capability`;
CREATE TABLE `Capability` (
  `ResourceId` int(11) NOT NULL,
  `Capability` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Company`
--

DROP TABLE IF EXISTS `Company`;
CREATE TABLE `Company` (
  `UserName` varchar(50) NOT NULL,
  `Headquarters` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `CostUnit`
--

DROP TABLE IF EXISTS `CostUnit`;
CREATE TABLE `CostUnit` (
  `CostUnitId` int(11) NOT NULL,
  `CostUnit` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `EmergencySupportFunctions`
--

DROP TABLE IF EXISTS `EmergencySupportFunctions`;
CREATE TABLE `EmergencySupportFunctions` (
  `ESFId` int(11) NOT NULL,
  `ESFName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `GovernmentAgency`
--

DROP TABLE IF EXISTS `GovernmentAgency`;
CREATE TABLE `GovernmentAgency` (
  `UserName` varchar(50) NOT NULL,
  `Jurisdiction` enum('Federal','State','Local') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Incidents`
--

DROP TABLE IF EXISTS `Incidents`;
CREATE TABLE `Incidents` (
  `IncidentId` int(11) NOT NULL,
  `IncidentDate` date NOT NULL,
  `Description` varchar(50) NOT NULL,
  `IncidentOwner` varchar(50) NOT NULL,
  `Latitude` decimal(8,6) NOT NULL,
  `Longitude` decimal(9,6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Individual`
--

DROP TABLE IF EXISTS `Individual`;
CREATE TABLE `Individual` (
  `UserName` varchar(50) NOT NULL,
  `JobTitle` varchar(30) NOT NULL,
  `HireDate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Municipality`
--

DROP TABLE IF EXISTS `Municipality`;
CREATE TABLE `Municipality` (
  `UserName` varchar(50) NOT NULL,
  `Population` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Repairs`
--

DROP TABLE IF EXISTS `Repairs`;
CREATE TABLE `Repairs` (
  `RepairId` int(11) NOT NULL,
  `ResourceId` int(11) NOT NULL,
  `StartOnDate` date NOT NULL,
  `ReadyBy` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Resources`
--

DROP TABLE IF EXISTS `Resources`;
CREATE TABLE `Resources` (
  `ResourceId` int(11) NOT NULL,
  `ResourceName` varchar(50) NOT NULL,
  `Model` varchar(50) DEFAULT NULL,
  `PrimaryESFId` int(11) NOT NULL,
  `Status` enum('Available','In Use','In Repair') NOT NULL,
  `ResourceOwner` varchar(50) NOT NULL,
  `Latitude` decimal(8,6) NOT NULL,
  `Longitude` decimal(9,6) NOT NULL,
  `CostAmount` decimal(10,2) NOT NULL,
  `CostUnitId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Resource_AdditionalESF`
--

DROP TABLE IF EXISTS `Resource_AdditionalESF`;
CREATE TABLE `Resource_AdditionalESF` (
  `ResourceId` int(11) NOT NULL,
  `AdditionalESFId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `UserRequestsResourcesForIncident`
--

DROP TABLE IF EXISTS `UserRequestsResourcesForIncident`;
CREATE TABLE `UserRequestsResourcesForIncident` (
  `RequestId` int(11) NOT NULL,
  `UserName` varchar(50) NOT NULL,
  `ResourceId` int(11) NOT NULL,
  `IncidentId` int(11) NOT NULL,
  `ReturnBy` date DEFAULT NULL,
  `StartDate` date DEFAULT NULL,
  `Action` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `USERS`
--

DROP TABLE IF EXISTS `USERS`;
CREATE TABLE `USERS` (
  `UserName` varchar(50) NOT NULL,
  `Name` varchar(50) NOT NULL,
  `Password` varchar(20) NOT NULL,
  `UserType` enum('Company','Individual','Municipality','GovernmentAgency','Admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Capability`
--
ALTER TABLE `Capability`
  ADD PRIMARY KEY (`ResourceId`,`Capability`);

--
-- Indexes for table `Company`
--
ALTER TABLE `Company`
  ADD PRIMARY KEY (`UserName`);

--
-- Indexes for table `CostUnit`
--
ALTER TABLE `CostUnit`
  ADD PRIMARY KEY (`CostUnitId`),
  ADD UNIQUE KEY `unique_CostUnit` (`CostUnit`);

--
-- Indexes for table `EmergencySupportFunctions`
--
ALTER TABLE `EmergencySupportFunctions`
  ADD PRIMARY KEY (`ESFId`);

--
-- Indexes for table `GovernmentAgency`
--
ALTER TABLE `GovernmentAgency`
  ADD PRIMARY KEY (`UserName`);

--
-- Indexes for table `Incidents`
--
ALTER TABLE `Incidents`
  ADD PRIMARY KEY (`IncidentId`),
  ADD KEY `IncidentOwner` (`IncidentOwner`);

--
-- Indexes for table `Individual`
--
ALTER TABLE `Individual`
  ADD PRIMARY KEY (`UserName`);

--
-- Indexes for table `Municipality`
--
ALTER TABLE `Municipality`
  ADD PRIMARY KEY (`UserName`);

--
-- Indexes for table `Repairs`
--
ALTER TABLE `Repairs`
  ADD PRIMARY KEY (`RepairId`),
  ADD KEY `ResourceId` (`ResourceId`);

--
-- Indexes for table `Resources`
--
ALTER TABLE `Resources`
  ADD PRIMARY KEY (`ResourceId`),
  ADD KEY `PrimaryESFId` (`PrimaryESFId`),
  ADD KEY `ResourceOwner` (`ResourceOwner`),
  ADD KEY `resources_ibfk_3` (`CostUnitId`);

--
-- Indexes for table `Resource_AdditionalESF`
--
ALTER TABLE `Resource_AdditionalESF`
  ADD PRIMARY KEY (`ResourceId`,`AdditionalESFId`),
  ADD KEY `AdditionalESFId` (`AdditionalESFId`);

--
-- Indexes for table `UserRequestsResourcesForIncident`
--
ALTER TABLE `UserRequestsResourcesForIncident`
  ADD PRIMARY KEY (`RequestId`),
  ADD UNIQUE KEY `UserName` (`UserName`,`ResourceId`,`IncidentId`),
  ADD KEY `ResourceId` (`ResourceId`),
  ADD KEY `IncidentId` (`IncidentId`);

--
-- Indexes for table `USERS`
--
ALTER TABLE `USERS`
  ADD PRIMARY KEY (`UserName`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `CostUnit`
--
ALTER TABLE `CostUnit`
  MODIFY `CostUnitId` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `Incidents`
--
ALTER TABLE `Incidents`
  MODIFY `IncidentId` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `Repairs`
--
ALTER TABLE `Repairs`
  MODIFY `RepairId` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `Resources`
--
ALTER TABLE `Resources`
  MODIFY `ResourceId` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `UserRequestsResourcesForIncident`
--
ALTER TABLE `UserRequestsResourcesForIncident`
  MODIFY `RequestId` int(11) NOT NULL AUTO_INCREMENT;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `Capability`
--
ALTER TABLE `Capability`
  ADD CONSTRAINT `capability_ibfk_1` FOREIGN KEY (`ResourceId`) REFERENCES `Resources` (`ResourceId`);

--
-- Constraints for table `Incidents`
--
ALTER TABLE `Incidents`
  ADD CONSTRAINT `incidents_ibfk_1` FOREIGN KEY (`IncidentOwner`) REFERENCES `Users` (`UserName`);

--
-- Constraints for table `Resources`
--
ALTER TABLE `Resources`
  ADD CONSTRAINT `resources_ibfk_1` FOREIGN KEY (`PrimaryESFId`) REFERENCES `EmergencySupportFunctions` (`ESFId`),
  ADD CONSTRAINT `resources_ibfk_2` FOREIGN KEY (`ResourceOwner`) REFERENCES `Users` (`UserName`),
  ADD CONSTRAINT `resources_ibfk_3` FOREIGN KEY (`CostUnitId`) REFERENCES `CostUnit` (`CostUnitId`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
