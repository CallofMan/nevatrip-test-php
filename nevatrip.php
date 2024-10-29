<?php
function createOrder($event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity) {
    // Подключение к БД
    $mysqli = new mysqli("localhost", "user", "password", "database");
    if ($mysqli->connect_errno) {
        die("Ошибка подключения: " . $mysqli->connect_error);
    }

    // Генерация уникального штрихкода
    $barcode = generateUniqueBarcode($mysqli);

    // Данные для бронирования
    $orderData = [
        'event_id' => $event_id,
        'event_date' => $event_date,
        'ticket_adult_price' => $ticket_adult_price,
        'ticket_adult_quantity' => $ticket_adult_quantity,
        'ticket_kid_price' => $ticket_kid_price,
        'ticket_kid_quantity' => $ticket_kid_quantity,
        'barcode' => $barcode,
    ];

    // Попытка бронирования
    while (true) {
        $bookingResponse = mockBookingAPI($orderData);

        if (isset($bookingResponse['message']) && $bookingResponse['message'] === 'order successfully booked') {
            break;
        } elseif (isset($bookingResponse['error']) && $bookingResponse['error'] === 'barcode already exists') {
            // Генерируем новый уникальный штрихкод
            $barcode = generateUniqueBarcode($mysqli);
            $orderData['barcode'] = $barcode;
        } else {
            die("Ошибка при бронировании: " . $bookingResponse['error']);
        }
    }

    // Подтверждение бронирования
    $approvalResponse = mockApprovalAPI(['barcode' => $barcode]);
    if (isset($approvalResponse['message']) && $approvalResponse['message'] === 'order successfully approved') {
        // Вычисление общей стоимости заказа
        $equal_price = ($ticket_adult_price * $ticket_adult_quantity) + ($ticket_kid_price * $ticket_kid_quantity);
        
        // Сохранение заказа в БД
        $stmt = $mysqli->prepare("INSERT INTO orders (event_id, event_date, ticket_adult_price, ticket_adult_quantity, ticket_kid_price, ticket_kid_quantity, barcode, equal_price, created) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("isiiisis", $event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity, $barcode, $equal_price);
        $stmt->execute();
        $stmt->close();
    } else {
        die("Ошибка подтверждения заказа: " . $approvalResponse['error']);
    }

    // Закрытие соединения с БД
    $mysqli->close();
}

function generateUniqueBarcode($mysqli) {
    do {
        $barcode = strval(random_int(10000000, 99999999));
        $result = $mysqli->query("SELECT id FROM orders WHERE barcode = '$barcode'");
    } while ($result->num_rows > 0);

    return $barcode;
}

function mockBookingAPI($data) {
    // Возвращаем мокированные данные для бронирования
    $responses = [
        ['message' => 'order successfully booked'],
        ['error' => 'barcode already exists']
    ];
    return $responses[array_rand($responses)];
}

function mockApprovalAPI($data) {
    // Возвращаем мокированные данные для подтверждения
    $responses = [
        ['message' => 'order successfully approved'],
        ['error' => 'event cancelled'],
        ['error' => 'no tickets'],
        ['error' => 'no seats'],
        ['error' => 'fan removed']
    ];
    return $responses[array_rand($responses)];
}

createOrder('11', '12-20-2001', 500, 2, 300, 3);
?>
