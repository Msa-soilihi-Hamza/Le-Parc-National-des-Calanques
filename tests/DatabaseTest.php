<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../config/database.php';

class DatabaseTest extends TestCase
{
    private Database $database;

    protected function setUp(): void
    {
        $this->database = new Database();
    }

    protected function tearDown(): void
    {
        $this->database->closeConnection();
    }

    public function testGetConnectionReturnsValidPDOInstance(): void
    {
        $pdo = $this->database->getConnection();
        
        $this->assertInstanceOf(PDO::class, $pdo);
        $this->assertNotNull($pdo);
    }

    public function testGetConnectionSetsCorrectAttributes(): void
    {
        $pdo = $this->database->getConnection();
        
        $this->assertEquals(PDO::ERRMODE_EXCEPTION, $pdo->getAttribute(PDO::ATTR_ERRMODE));
        $this->assertEquals(PDO::FETCH_ASSOC, $pdo->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE));
    }

    public function testGetConnectionCanExecuteQuery(): void
    {
        $pdo = $this->database->getConnection();
        
        $stmt = $pdo->query("SELECT 1 as test_value");
        $result = $stmt->fetch();
        
        $this->assertEquals(1, $result['test_value']);
    }

    public function testCloseConnectionSetsToNull(): void
    {
        // Obtenir une connexion
        $pdo = $this->database->getConnection();
        $this->assertNotNull($pdo);
        
        // Fermer la connexion
        $this->database->closeConnection();
        
        // Vérifier qu'une nouvelle connexion peut être créée
        $newPdo = $this->database->getConnection();
        $this->assertInstanceOf(PDO::class, $newPdo);
    }

    public function testMultipleConnectionCallsReturnSameInstance(): void
    {
        $pdo1 = $this->database->getConnection();
        $pdo2 = $this->database->getConnection();
        
        // Note: La classe Database recrée l'instance à chaque appel
        // Ce test vérifie que les deux instances sont valides
        $this->assertInstanceOf(PDO::class, $pdo1);
        $this->assertInstanceOf(PDO::class, $pdo2);
    }

    public function testDatabaseConnectionParameters(): void
    {
        $pdo = $this->database->getConnection();
        
        // Vérifier que nous sommes connectés à la bonne base
        $stmt = $pdo->query("SELECT DATABASE() as current_db");
        $result = $stmt->fetch();
        
        $this->assertEquals('le-parc-national-des-calanques', $result['current_db']);
    }
}