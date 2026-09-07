<?php

declare(strict_types=1);

function logAction(PDO $database, int $userId, string $action, string $entityType, ?int $entityId = null, array $metadata = []): void
{
    $statement = $database->prepare(
        'INSERT INTO audit_log (user_id, action, entity_type, entity_id, metadata)
         VALUES (:user_id, :action, :entity_type, :entity_id, :metadata)'
    );
    $statement->execute([
        'user_id' => $userId,
        'action' => $action,
        'entity_type' => $entityType,
        'entity_id' => $entityId,
        'metadata' => $metadata === [] ? null : json_encode($metadata, JSON_THROW_ON_ERROR),
    ]);
}
