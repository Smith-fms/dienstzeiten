<?php

namespace OCA\DienstzeitenApp\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Dienstzeit extends Entity implements JsonSerializable {
    
    protected $userId;
    protected $firstName;
    protected $lastName;
    protected $email;
    protected $serviceDate;
    protected $startTime;
    protected $endTime;
    protected $station;
    protected $otherDetails;
    protected $overtimeDueToEmergency;
    protected $emergencyNumber;
    protected $signature;
    protected $createdAt;
    protected $status;
    protected $rejectionReason;
    protected $approvedBy;
    protected $approvedAt;
    protected $token;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('overtimeDueToEmergency', 'integer');
        $this->addType('serviceDate', 'date');
        $this->addType('startTime', 'time');
        $this->addType('endTime', 'time');
        $this->addType('createdAt', 'datetime');
        $this->addType('approvedAt', 'datetime');
    }

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'service_date' => $this->serviceDate,
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'station' => $this->station,
            'other_details' => $this->otherDetails,
            'overtime_due_to_emergency' => $this->overtimeDueToEmergency,
            'emergency_number' => $this->emergencyNumber,
            'signature' => $this->signature,
            'created_at' => $this->createdAt,
            'status' => $this->status,
            'rejection_reason' => $this->rejectionReason,
            'approved_by' => $this->approvedBy,
            'approved_at' => $this->approvedAt,
        ];
    }
}
