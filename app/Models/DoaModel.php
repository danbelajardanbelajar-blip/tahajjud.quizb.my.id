<?php
namespace app\Models;

class DoaModel {
    private $dataFile;

    public function __construct() {
        $this->dataFile = ROOT_DIR . '/data_doa.json';
    }

    public function getAll() {
        if (!file_exists($this->dataFile)) {
            return [];
        }
        $json = file_get_contents($this->dataFile);
        return json_decode($json, true) ?? [];
    }

    public function getById($id) {
        $data = $this->getAll();
        foreach ($data as $item) {
            if ($item['id'] === $id) {
                return $item;
            }
        }
        return null;
    }

    public function save($arab, $terjemah, $repetitions = 3, $id = null) {
        // Validation: Ensure UTF-8
        if (!mb_check_encoding($arab, 'UTF-8') || !mb_check_encoding($terjemah, 'UTF-8')) {
            throw new \Exception("Invalid UTF-8 encoding");
        }

        // Validate Arabic field (strip any Latin/Cyrillic chars)
        // Allow Arabic letters, marks, punctuation, numbers, spaces
        $arab = preg_replace('/[^\p{Arabic}\p{M}\p{P}\p{N}\s]/u', '', $arab);
        // Trim spaces
        $arab = trim($arab);
        $terjemah = trim($terjemah);
        // Ensure repetitions is an integer
        $repetitions = (int)$repetitions;
        if ($repetitions < 1) $repetitions = 3;

        if (empty($arab)) {
            throw new \Exception("Teks Arab tidak boleh kosong atau invalid.");
        }

        $fp = fopen($this->dataFile, 'c+');
        if (flock($fp, LOCK_EX)) { // Acquire exclusive lock
            $filesize = filesize($this->dataFile);
            $json = $filesize > 0 ? fread($fp, $filesize) : '[]';
            $data = json_decode($json, true) ?? [];

            if ($id) {
                // Update
                $found = false;
                foreach ($data as &$item) {
                    if ($item['id'] === $id) {
                        $item['arab'] = $arab;
                        $item['terjemah'] = $terjemah;
                        $item['repetitions'] = $repetitions;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    flock($fp, LOCK_UN);
                    fclose($fp);
                    throw new \Exception("ID tidak ditemukan");
                }
            } else {
                // Create
                $id = uniqid();
                $data[] = [
                    'id' => $id,
                    'arab' => $arab,
                    'terjemah' => $terjemah,
                    'repetitions' => $repetitions
                ];
            }

            // Backup before overwrite
            copy($this->dataFile, $this->dataFile . '.bak');

            // Overwrite file
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            fflush($fp);
            flock($fp, LOCK_UN);
            fclose($fp);

            return ['id' => $id, 'arab' => $arab, 'terjemah' => $terjemah, 'repetitions' => $repetitions];
        } else {
            fclose($fp);
            throw new \Exception("Gagal mengunci file database. Silakan coba lagi.");
        }
    }

    public function delete($id) {
        $fp = fopen($this->dataFile, 'c+');
        if (flock($fp, LOCK_EX)) {
            $filesize = filesize($this->dataFile);
            $json = $filesize > 0 ? fread($fp, $filesize) : '[]';
            $data = json_decode($json, true) ?? [];

            $newData = array_values(array_filter($data, function($item) use ($id) {
                return $item['id'] !== $id;
            }));

            if (count($newData) === count($data)) {
                flock($fp, LOCK_UN);
                fclose($fp);
                throw new \Exception("Data tidak ditemukan");
            }

            // Backup
            copy($this->dataFile, $this->dataFile . '.bak');

            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, json_encode($newData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            fflush($fp);
            flock($fp, LOCK_UN);
            fclose($fp);
            return true;
        } else {
            fclose($fp);
            throw new \Exception("Gagal mengunci file database");
        }
    }

    public function reorderData(array $newOrderIds) {
        $fp = fopen($this->dataFile, 'c+');
        if (flock($fp, LOCK_EX)) {
            $filesize = filesize($this->dataFile);
            $json = $filesize > 0 ? fread($fp, $filesize) : '[]';
            $data = json_decode($json, true) ?? [];

            $dataById = [];
            foreach ($data as $item) {
                $dataById[$item['id']] = $item;
            }

            $newData = [];
            foreach ($newOrderIds as $id) {
                if (isset($dataById[$id])) {
                    $newData[] = $dataById[$id];
                    unset($dataById[$id]);
                }
            }
            foreach ($dataById as $item) {
                $newData[] = $item;
            }

            copy($this->dataFile, $this->dataFile . '.bak');

            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, json_encode($newData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            fflush($fp);
            flock($fp, LOCK_UN);
            fclose($fp);
            return true;
        } else {
            fclose($fp);
            throw new \Exception("Gagal mengunci file database");
        }
    }
}
