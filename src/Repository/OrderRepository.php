<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
* @extends ServiceEntityRepository<Order>
*
* @method Order|null find($id, $lockMode = null, $lockVersion = null)
* @method Order|null findOneBy(array $criteria, array $orderBy = null)
* @method Order[]    findAll()
* @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
*/
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function findOrderById($orderId)
    {
        return $this->find($orderId);
    }

    public function findByStatus(string $status): array
    {
        $orders = $this->createQueryBuilder('o')
            ->andWhere('o.status = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getResult();

        return array_map([$this, 'formatOrderData'], $orders);
    }

    public function findByIdInFormat(int $orderId): ?array
    {
        $order = $this->find($orderId);
        if ($order) {
            return [$this->formatOrderData($order)];
        }
        return [];
    }

    private function formatOrderData(Order $order): array
    {
        $orderItemsData = [];
        foreach ($order->getOrderItems() as $orderItem) {
            $orderItemsData[] = [
                'item_id' => $orderItem->getItemId(),
                'item_name' => $orderItem->getItemName(),
                'quantity' => $orderItem->getQuantity(),
            ];
        }

        $orderData['order_items'] = $orderItemsData;
        return [
            'id' => $order->getId(),
            'delivery_address' => $order->getDeliveryAddress(),
            'delivery_date' => $order->getDeliveryDate(),
            'order_items' => $orderData['order_items'],
            'status' => $order->getStatus(),
        ];
    }
}
