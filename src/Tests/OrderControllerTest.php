<?php

namespace App\Tests;

use App\Entity\Order;
use PHPUnit\Framework\TestCase;
use App\Repository\ItemRepository;
use Doctrine\ORM\EntityRepository;
use App\Controller\OrderController;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderControllerTest extends TestCase
{
    protected $entityManagerMock;
    protected $orderController;
    protected $itemRepositoryMock;
    protected $orderRepositoryMock;
    
    protected function setUp(): void
    {
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->itemRepositoryMock = $this->createMock(ItemRepository::class);
        $this->orderRepositoryMock = $this->createMock(OrderRepository::class);
        $this->orderController = new OrderController($this->entityManagerMock,$this->itemRepositoryMock,$this->orderRepositoryMock);
    }
    
    public function testCreateOrder()
    {
        $requestData = [
            'name' => 'Test Product',
            'deliveryAddress' => 'Test Address',
            'deliveryOption' => 'Test Option',
            'deliveryDate' => '2024-04-04 00:00:00',
            'orderItems' => '[{\"item_id\":1,\"quantity\":2}]'
        ];
        
        $request = new Request([], [], [], [], [], [], json_encode($requestData));
        
        $this->entityManagerMock->expects($this->once())
        ->method('persist');
        
        $this->entityManagerMock->expects($this->once())
        ->method('flush')
        ->willReturn(null);
        
        $response = $this->orderController->create($request);
        
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }
    
    public function testGetOrdersByStatus()
    {
        $request = Request::create('/api/orders', 'GET', ['status' => Order::$status_processing]);
        $repositoryMock = $this->createMock(\Doctrine\ORM\EntityRepository::class);
        $this->entityManagerMock->expects($this->once())
        ->method('getRepository')
        ->willReturn($repositoryMock);
        
        $response = $this->orderController->getOrders($request);
        
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }
    
    public function testUpdateOrderStatus()
    {
        $requestData = [
            'orderId' => 8, 
            'status' => Order::$status_processing
        ];
        
        $request = new Request([], [], [], [], [], [], json_encode($requestData));
        
        $orderMock = $this->createMock(Order::class);
        $orderMock->expects($this->once())
        ->method('setStatus')
        ->with('processing');
        
        $orderRepositoryMock = $this->createMock(EntityRepository::class);
        $orderRepositoryMock->expects($this->once())
        ->method('find')
        ->with($requestData['orderId'])
        ->willReturn($orderMock);
        
        $this->entityManagerMock->expects($this->once())
        ->method('getRepository')
        ->with(Order::class)
        ->willReturn($orderRepositoryMock);
        
        $response = $this->orderController->updateOrderStatus($request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }
}
