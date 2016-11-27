<?php
	//This file will handle all SQL functions in the project
	//(1) Login
	function selectUserStmt($inputUserName)
	{
	    return "SELECT Name, Password, UserType FROM Users WHERE UserName = '$inputUserName'";
	}

	function selectCompanyStmt($inputUserName)
	{
		return "SELECT Headquarters FROM Company WHERE UserName = '$inputUserName'";
	}

	function selectGovernmentStmt($inputUserName)
	{
		return "SELECT Jurisdiction FROM GovernmentAgency WHERE UserName = '$inputUserName'";
	}

	function selectMunicipalityStmt($inputUserName)
	{
		return "SELECT Population FROM Municipality WHERE UserName = '$inputUserName'";
	}

	//(2) Add Resource

	function selectESFStmt()
	{
		return "SELECT ESFId, ESFName FROM EmergencySupportFunctions";
	}

	function selectCostUnitStmt()
	{
		return "SELECT CostUnitId, CostUnit FROM CostUnit";
	}

	function insertResourceStmt($resourceName, $resourceModel, $primaryESFId, $resourceStatus, $loginUser, $homeLatitude, 
		                           $homeLongitude, $costAmt, $costPerUnit)
	{
		return "INSERT INTO Resources (ResourceName, Model, PrimaryESFId, Status, ResourceOwner, Latitude, Longitude, CostAmount,  CostUnitId) " .
               "VALUES ('$resourceName', '$resourceModel', $primaryESFId, '$resourceStatus', '$loginUser', $homeLatitude, $homeLongitude, 
                $costAmt, $costPerUnit)";
	}

	function insertSecondaryESFStmt($selectedSecondaryESFs)
	{
        return "INSERT INTO Resource_AdditionalESF (ResourceId, AdditionalESFId) 
                VALUES " . trim($selectedSecondaryESFs,',');
	}

	function insertCapabilityStmt($selectedCapabilities)
	{
		return "INSERT INTO Capability (ResourceId, Capability) 
                VALUES " . trim($selectedCapabilities,',');
    }

	//(3) Add Emergency Incident
	function insertIncidentStmt($incName, $incDate, $loginUser, $incLatitude, $incLongitude)
	{
		return "INSERT INTO Incidents (Description, IncidentDate, IncidentOwner, Latitude, Longitude) " .
               "VALUES ('$incName', '$incDate', '$loginUser', $incLatitude, $incLongitude)";
	}

	//(4) Search Resources

	//selectESFStmt() from add resource section is re-used here too...

	function selectIncidentsStmt($loginUser)
	{
		return "SELECT IncidentId, Description, Latitude, Longitude 
                FROM Incidents 
                WHERE IncidentOwner = '$loginUser'";
	}

	//(5) Search Resources Results

	function queryResourcesStmt($selectedIncidentLat, $selectedIncidentLong, $keywordSearch, $selectedESFId, $incDistance)
	{

    	/*Apply Haversine formula to compute distance...*/
		if($selectedIncidentLat === 'NULL' or $selectedIncidentLong === 'NULL'){
    		$queryCalcDistance = "-1 AS Distance ";
		}
		else {
	        $queryCalcDistance = "@a := POWER(SIN(RADIANS(R.Latitude - $selectedIncidentLat)/2), 2) + 
	                                    COS(RADIANS($selectedIncidentLat)) * COS(RADIANS(R.Latitude)) * 
	                                    POWER(SIN(RADIANS(R.Longitude - $selectedIncidentLong)/2),2), 
	                              @c := 2 * ATAN2(SQRT(@a), SQRT(1-@a)),  
	                              @Distance := ROUND(6371*@c, 2) AS Distance ";
		}

	    return "SELECT DISTINCT R.ResourceId, R.ResourceName, Users.Name AS ResourceOwner, R.CostAmount, CostUnit.CostUnit, 
	    					   R.Status, ReqDep.StartDate, ReqDep.ReturnBy AS ReturnDate, Rep.ReadyBy AS ReadyBy, " . $queryCalcDistance .
                "FROM Resources AS R
                  INNER JOIN Users ON (
                    R.ResourceOwner = Users.UserName
                    )
                  INNER JOIN CostUnit ON (
                    R.CostUnitId = CostUnit.CostUnitId
                    )
                  LEFT OUTER JOIN Resource_AdditionalESF AS RA ON (
                    R.ResourceId = RA.ResourceId
                    )
                  LEFT OUTER JOIN Capability AS Cap ON (
                    R.ResourceId = Cap.ResourceId
                    )
                  LEFT OUTER JOIN UserRequestsResourcesForIncident AS ReqDep ON (
                    R.ResourceId = ReqDep.ResourceId AND
                    ReqDep.Action = 'deploy' AND
                    ReqDep.ReturnBy > CURRENT_DATE
                    )
                  LEFT OUTER JOIN Repairs AS Rep ON (
                    R.ResourceId = Rep.ResourceId AND
                    Rep.ReadyBy IS NOT NULL AND
                    Rep.ReadyBy > CURRENT_DATE
                    )
                WHERE
                  ((IF(LENGTH('$keywordSearch') > 0, R.ResourceName LIKE '%$keywordSearch%', 1)) OR
                  (IF(LENGTH('$keywordSearch') > 0, R.Model LIKE '%$keywordSearch%', 1)) OR
                  (IF(LENGTH('$keywordSearch') > 0, Cap.Capability LIKE '%$keywordSearch%', 1))) AND
                  ((IF ($selectedESFId > 0, R.PrimaryESFId = $selectedESFId, 1)) OR
                  (IF ($selectedESFId > 0, RA.AdditionalESFId = $selectedESFId, 1)))
                HAVING Distance < $incDistance
                ORDER BY Distance, ResourceId ASC";
	}

	function updateResourceStmt($resourceId, $resourceStatus)
	{
		return "UPDATE Resources SET Status = '$resourceStatus' WHERE ResourceId = $resourceId";

	}

	function requestResourceStmt($loginUser, $resourceId, $selectedIncidentId, $currentDate, $returnDate, $action)
	{
		return "INSERT INTO UserRequestsResourcesForIncident (UserName, ResourceId, IncidentId, StartDate, ReturnBy, Action)
                VALUES ('$loginUser', $resourceId, $selectedIncidentId, '$currentDate', '$returnDate', '$action')";
	}

	function repairResourceStmt($resourceId, $startOnDate, $readyBy)
	{
		return "INSERT INTO Repairs (ResourceId, StartOnDate, ReadyBy) 
                VALUES ($resourceId, '$startOnDate', '$readyBy')";
    }

    //(6)Resource Status 

	function queryUsedResStmt($loginUser)
	{
		return "SELECT R.ResourceId, R.ResourceName, R.Status, I.IncidentId, I.Description, 
				 U.Name, U.UserName, UR.StartDate, R.ResourceOwner, UR.ReturnBy
		         FROM UserRequestsResourcesForIncident AS UR
		           INNER JOIN Incidents AS I ON (
		                I.IncidentId = UR.IncidentId
		           )
		           INNER JOIN Resources AS R ON (
		                R.ResourceId = UR.ResourceId
		           )
		           INNER JOIN Users AS U ON (
		                R.ResourceOwner = U.UserName
		           )
		          WHERE I.IncidentOwner = '$loginUser'
		          AND UR.Action = 'deploy'
		          AND UR.ReturnBy > CURRENT_DATE
		          ORDER BY R.ResourceId ASC";
	}

	function queryRequestedResStmt($loginUser)
	{
		return "SELECT R.ResourceId, R.ResourceName, R.Status, I.IncidentId, I.Description, U.Name, 
				U.UserName, UR.StartDate, R.ResourceOwner, UR.ReturnBy
	              FROM UserRequestsResourcesForIncident AS UR
	              INNER JOIN Incidents AS I ON (
	                    UR.IncidentId = I.IncidentId
	              )
	              INNER JOIN Resources AS R ON (
	                    UR.ResourceId = R.ResourceId
	              )
	              INNER JOIN Users AS U ON (
	                    R.ResourceOwner = U.UserName
	               )
	              WHERE UR.UserName = '$loginUser'
	              AND UR.Action = 'request'
	              ORDER BY R.ResourceId ASC";
	}

	function queryReceivedReqStmt($loginUser)
	{
		return "SELECT R.ResourceId, R.ResourceName, R.Status, I.IncidentId, I.Description, U.Name, 
				U.UserName, R.ResourceOwner, UR.ReturnBy
                 FROM UserRequestsResourcesForIncident AS UR
                   INNER JOIN Incidents AS I ON (
                        UR.IncidentId = I.IncidentId
                   )
                   INNER JOIN Resources AS R ON (
                        UR.ResourceId = R.ResourceId
                   )
                   INNER JOIN Users AS U ON (
                        UR.UserName = U.UserName
                   )
                  WHERE R.ResourceOwner = '$loginUser'
                  AND UR.Action = 'request'
                  ORDER BY R.ResourceId ASC";
	}

	function queryRepairsStmt($loginUser)
	{
		return "SELECT RE.ResourceId, R.ResourceName, RE.StartOnDate, RE.ReadyBy 
                 FROM Repairs AS RE
                 INNER JOIN Resources AS R ON
                 RE.ResourceId = R.ResourceId
                 WHERE R.ResourceOwner = '$loginUser'
                 AND RE.ReadyBy IS NOT NULL
                 AND RE.ReadyBy > CURRENT_DATE
                 ORDER BY R.ResourceId ASC";

	}

	function updateRequestsResourcesForIncidentStmt($loginUser, $resourceId, $incidentId, $action)
	{
	    if($action === 'deploy')
	    {
    		return "UPDATE UserRequestsResourcesForIncident
		            SET Action = '$action'
		            WHERE UserName = '$loginUser'
		            AND ResourceId = $resourceId
		            AND IncidentId = $incidentId";

	    }
	    else
	    {
    		return "UPDATE UserRequestsResourcesForIncident
		            SET Action = '$action', ReturnBy = NULL
		            WHERE UserName = '$loginUser'
		            AND ResourceId = $resourceId
		            AND IncidentId = $incidentId";

	    }

	}

	function cancelResourceRepairRequestStmt($resourceId)
	{
		return "UPDATE Resources
		        SET Status = 'Available'
		        WHERE ResourceId = $resourceId
		        AND Status = 'In Repair'";
	}

	function cancelRepairStmt($resourceId)
	{
		return "UPDATE Repairs
                SET ReadyBy = NULL
                WHERE ResourceId = $resourceId";
	}

	//Resource Report
	function queryResourceReport($loginUser)
	{
	    return "SELECT PESF.ESFId, PESF.ESFName,
				  COUNT(CASE WHEN R.PrimaryESFId = PESF.ESFId THEN 1 END) AS Total_Resources,
				  COUNT(CASE WHEN R.Status = 'In Use' AND UR.Action = 'deploy' AND UR.ReturnBy > CURRENT_DATE THEN 1 END) AS Resources_In_Use
				FROM EmergencySupportFunctions AS PESF
				  LEFT OUTER JOIN Resources AS R ON (R.PrimaryESFId = PESF.ESFId AND R.ResourceOwner = '$loginUser')
				  LEFT OUTER JOIN UserRequestsResourcesForIncident AS UR ON (R.ResourceId = UR.ResourceId AND UR.Action = 'deploy' AND UR.ReturnBy > CURRENT_DATE)
				GROUP BY PESF.ESFId, PESF.ESFName WITH ROLLUP";

	}

	
