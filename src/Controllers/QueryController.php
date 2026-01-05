<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;
use PDOException;

class QueryController extends Controller
{
    public function execute()
    {
        $data = $this->getInput();

        if (!isset($data['query'])) {
            return $this->jsonResponse(['error' => 'Missing query field'], 400);
        }

        $sql = $data['query'];
        $db = Database::getInstance()->getConnection();

        try {
            // Check if it's a SELECT query to return results
            if (stripos(trim($sql), 'SELECT') === 0) {
                $stmt = $db->query($sql);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                return $this->jsonResponse(['results' => $results]);
            } else {
                // For INSERT, UPDATE, DELETE, etc.
                $affected = $db->exec($sql);
                return $this->jsonResponse(['message' => 'Query executed', 'affected_rows' => $affected]);
            }
        } catch (PDOException $e) {
            return $this->jsonResponse(['error' => 'Query failed: ' . $e->getMessage()], 500);
        }
    }
}
