<?php
class ModelExtensionPaymentSensebank extends Model {
    public function getMethod($address, $total) {
        $this->load->language('extension/payment/sensebank');
        $method_data = array(
            'code'     => 'sensebank',
            'title'    => $this->language->get('entry_sensebank_text_title'),
            'terms'      => '',
            'sort_order' => $this->config->get('payment_sensebank_sort_order')
        );
        return $method_data;
    }

    public function storeGatewayOrder($data) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "sensebank_order` SET `order_id` = '" . (int)$data['order_id'] . "', `gateway_order_reference` = '" . $this->db->escape($data['gateway_order_reference']) . "', `currency` = '" . $this->db->escape($data['currency']) . "', `order_amount` = '" . (float)$data['order_amount'] . "', `order_amount_deposited` = '" . (float)$data['order_amount_deposited'] . "', `status_created` = '" . (int)$data['status_created'] . "', `date_added` = NOW(), `date_updated` = NOW()");
    }

    public function getGatewayOrderByReference($orderId) {
        $result = $this->db->query("SELECT * FROM `" . DB_PREFIX . "sensebank_order` WHERE gateway_order_reference = '" . $orderId . "' LIMIT 1");
        return $result->row;
    }

    public function getGatewayOrders() {
        $result = $this->db->query("SELECT * FROM `" . DB_PREFIX . "sensebank_order` WHERE status_deposited = 0");
        return $result;
    }

    public function updateGatewayOrder($order_id, $data) {
        $sql = "UPDATE `" . DB_PREFIX . "sensebank_order` SET ";
        $sql_data = array();
        if (isset($data['order_amount_deposited'])) {
            $sql_data[] = "`order_amount_deposited` = '" . (float)$data['order_amount_deposited'] . "'";
        }
        if (isset($data['order_amount_refunded'])) {
            $sql_data[] = "`order_amount_refunded` = '" . (float)$data['order_amount_refunded'] . "'";
        }
        if (isset($data['status_deposited'])) {
            $sql_data[] = "`status_deposited` = " . (int)$data['status_deposited'];
        }
        if (isset($data['status_reversed'])) {
            $sql_data[] = "`status_reversed` = " . (int)$data['status_reversed'];
        }
        if (isset($data['status_refunded'])) {
            $sql_data[] = "`status_refunded` = " . (int)$data['status_refunded'];
        }
        if (isset($data['status'])) {
            $sql_data[] = "`status` = " . (int)$data['status'];
        }
        $sql_data[] = "`date_updated` = NOW()";
        $sql .= implode(', ', $sql_data);
        $sql .= " WHERE `order_id` = " . (int)$order_id;
        $this->db->query($sql);
    }
}