<?php

    declare(strict_types=1);

    namespace FFGBSY\Services;

    use DI\ContainerBuilder;
    use Psr\Container\ContainerInterface;
    use PDO;
    use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
    use Mike42\Escpos\PrintConnectors\FilePrintConnector;
    use Mike42\Escpos\Printer;
    use Mike42\Escpos\EscposImage;

    final class DruckerService extends BaseService
    {
        public function create($data)
        {
            $sth = $this->db->prepare("INSERT INTO drucker (name, ip, port, mac) VALUES (:name, :ip, :port, :mac)");
            $sth->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $sth->bindParam(':ip', $data['ip'], PDO::PARAM_STR);
            $sth->bindParam(':port', $data['port'], PDO::PARAM_INT);
            $sth->execute();

            return $this->read($this->db->lastInsertId());
        }

        public function read($id = null)
        {
            if ($id != null)
            {
                $sth = $this->db->prepare("SELECT * FROM drucker WHERE id=:id");
                $sth->bindParam(':id', $id, PDO::PARAM_INT);
                return $this->singleRead($sth);
            }
            else
            {
                $sth = $this->db->prepare("SELECT * FROM drucker");
                return $this->multiRead($sth);
            }
        }

        public function checkConnections()
        {
            $drucker = $this->read();
            $results = [];

            foreach($drucker as $drucker)
            {
                $result = new \stdClass();
                $result->drucker = $drucker;
                $result->result = $this->connectPosPrinter($drucker, 2);
                array_push($results, $result);
            }

            return $results;
        }

        public function checkConnection($id)
        {
            $drucker = $this->read($id);
            $result = new \stdClass();
            $result->drucker = $drucker;
            $result->result = $this->connectPosPrinter($drucker, 2);

            return $result;
        }

        private function connectPosPrinter($drucker, $timeout = 3)
        {
            try{
                $connector = new NetworkPrintConnector($drucker->ip, $drucker->port, $timeout);
                $printer = new Printer($connector);
                $printer->close();
                return true;
            } catch (\Exception $e)
            {
                return false;
            }
            return false;
        }

        public function update($data)
        {
            $sth = $this->db->prepare("UPDATE drucker SET name = :name, ip = :ip, port = :port WHERE id = :id");
            $sth->bindParam(':id', $data['id'], PDO::PARAM_INT);
            $sth->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $sth->bindParam(':ip', $data['ip'], PDO::PARAM_STR);
            $sth->bindParam(':port', $data['port'], PDO::PARAM_INT);
            $sth->execute();
            
            return $this->read($data['id']);
        }

        public function delete($id)
        {
            $sth = $this->db->prepare("DELETE FROM drucker WHERE id = :id");
            $sth->bindParam(':id', $id, PDO::PARAM_INT);
            return $sth->execute();
        }

        protected function singleMap($obj)
        {
            $obj->id = $this->asNumber($obj->id);
            $obj->port = $this->asNumber($obj->port);
            return $obj;
        }
    }
