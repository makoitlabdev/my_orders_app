<?php

namespace App\Controller;

use App\Entity\Item;
use App\Entity\Order;
use AppBundle\UserDTO;
use AppBundle\UserQuery;
use App\Entity\OrderItem;
use OpenApi\Attributes as OA;
use App\Repository\ItemRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderController extends AbstractController
{
    public function __construct(EntityManagerInterface $entityManager,ItemRepository $itemRepository,
    OrderRepository $orderRepository)
    {
        $this->entityManager = $entityManager;
        $this->itemRepository = $itemRepository;
        $this->orderRepository = $orderRepository;
    }
    
    public function createResponse(int $statusCode, string $message, array $data = []): Response
    {
        $responseData = [
            'message' => $message,
            'data' => $data,
        ];
        return new JsonResponse($responseData, $statusCode);
    }
    
    #[OA\Tag(name: 'Orders')]
    #[OA\RequestBody(content: new OA\JsonContent(
        required: ['name','deliveryAddress','deliveryOption','deliveryDate','orderItems'],
        properties: [
            new OA\Property(property: 'name', description: 'Name of the order', type: 'string', example: 'Product'),
            new OA\Property(property: 'deliveryAddress', description: 'Delivery address', type: 'string', example: '145, Bonville, US'),
            new OA\Property(property: 'deliveryOption', description: 'Delivery option', type: 'string', example: 'Cash Settlement'),
            new OA\Property(property: 'deliveryDate', description: 'Delivery date', type: 'date-time', example: '2024-04-04 00:00:00'),
            new OA\Property(property: 'orderItems', description: 'Order Items', type: 'string', example: '[{"itemId":1,"quantity":2}]'),]
            ))]
            
            #[OA\Response(ref: '#/components/responses/CreateOrderResponse', response: 201)]
            #[OA\Response(response: 404,description: 'Item not found')]
            #[OA\Response(response: 422,description: 'Invalid Argument')]
            #[OA\Response(response: 500,description: 'Internal server error')]
            
            public function create(Request $request): Response
            {
                try{
                    $data = json_decode($request->getContent(), true);
                    
                    // Validate request data 
                    if (!isset($data['name']) || !is_string($data['name'])) {
                        return $this->createResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'Name must be provided and must be a string.');
                    }
                    
                    if (!isset($data['deliveryAddress']) || !is_string($data['deliveryAddress'])) {
                        return $this->createResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'Delivery address must be provided and must be a string.');
                    }
                    
                    if (!isset($data['deliveryOption']) || !is_string($data['deliveryOption'])) {
                        return $this->createResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'Delivery option must be provided and must be a string.');
                    }
                    
                    if (null === $data['deliveryDate']) {
                        return $this->createResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'Delivery date must be provided and must be a string.');
                    }
                    
                    $date = \DateTime::createFromFormat('Y-m-d H:i:s', $data['deliveryDate']);
                    if (!$date) {
                        return $this->createResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'Delivery date must be in datetime format (YYYY-MM-DD HH:MM:SS).');
                    }
                    
                    $order = new Order();
                    $order->setName($data['name']);
                    $order->setDeliveryAddress($data['deliveryAddress']);
                    $order->setDeliveryOption($data['deliveryOption']);
                    $order->setDeliveryDate(new \DateTime($data['deliveryDate']));
                    
                    $orderItems = json_decode($data['orderItems'], true);
                    
                    $this->entityManager->persist($order);
                    $this->entityManager->flush();
                    
                    $orderId = $order->getId();
                    
                    foreach ($orderItems as $item) {
                        $orderItem = new OrderItem();
                        $itemEntity = $this->itemRepository->findItemById($item['itemId']);
                        $orderEntity = $this->orderRepository->findOrderById($orderId);
                        
                        if (null === $itemEntity) {
                            return $this->createResponse(Response::HTTP_NOT_FOUND, 'Item not found');
                        }
                        
                        $orderItem->setItemId($itemEntity);
                        $orderItem->setOrderId($orderEntity);
                        $orderItem->setQuantity($item['quantity']);
                        $this->entityManager->persist($orderItem);
                        $this->entityManager->flush();
                    }
                    return $this->createResponse(Response::HTTP_CREATED, 'Order created');
                } catch (\Throwable $e) {
                    return $this->createResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Failed to create new order');
                }
            }
            
            #[Route('/api/orders', name: 'get_orders', methods: ['GET'])]
            #[OA\Tag(name: 'Orders', description:"Enter Order ID or Select Order Status")]
            #[OA\Parameter(name: "orderId", in: "query", schema: new OA\Schema(type: "integer"))]
            #[OA\Parameter(name: "status", in: "query", schema: new OA\Schema(type: "string", enum: ["processing", "delivered"]))]
            #[OA\Response(ref: '#/components/responses/GetOrdersDataResponse', response: 200)]
            #[OA\Response(response: 404,description: 'Order not found')]
            #[OA\Response(response: 422,description: 'Invalid Argument')]
            #[OA\Response(response: 500,description: 'Internal server error')]
            
            public function getOrders(Request $request) : Response
            {
                try{
                    $status = $request->query->get('status');
                    $orderId = $request->query->get('orderId');
                    
                    // Validate data
                    if(isset($status)){
                        if (!is_string($status)) {
                            return $this->createResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'Status must be a string.');
                        }
                        
                        if ($status != Order::$status_processing && $status != Order::$status_delivered) {
                            return $this->createResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'The status can be either processing or delivered');
                        }
                    }
                    
                    if (isset($orderId) && !is_numeric($orderId)) {
                        return $this->createResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'Order ID must be integer.');
                    }
                    
                    if (!isset($status) && !isset($orderId)) {
                        return $this->createResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'Either status or order ID must be provided.');
                    }
                    
                    $repository = $this->entityManager->getRepository(Order::class);
                    
                    if (!is_null($status)) {
                        $orders = $this->orderRepository->findByStatus($status);
                    } elseif (!is_null($orderId)) {
                        $orderRepository = $this->entityManager->getRepository(Order::class);
                        $order = $orderRepository->find($orderId);
                        
                        if (!$order) {
                            return $this->createResponse(Response::HTTP_NOT_FOUND, 'Order not found');
                        }
                        $orders = $this->orderRepository->findByIdInFormat($orderId);
                    } else {
                        return $this->createResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'Select either Order ID or Status');
                    }
                    return $this->createResponse(Response::HTTP_OK, 'Success',$orders);
                } catch (\Throwable $e) {
                    return $this->createResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Success', $orders);
                }
            }
            
            #[Route('/api/orders', name: 'update_status', methods: ['PUT'])]
            #[OA\Tag(name: 'Orders')]
            #[OA\RequestBody(content: new OA\JsonContent(
                required: ['orderId','status'],
                properties: [
                    new OA\Property(property: 'orderId', description: 'Order ID', type: 'integer', example: '2'),
                    new OA\Property(property: 'status', description: 'New Status', type: 'string', example: 'delivered')]
                    ))]
                    #[OA\Response(ref: '#/components/responses/UpdateOrderResponse', response: 200)]
                    #[OA\Response(response: 404,description: 'Order not found')]
                    #[OA\Response(response: 422,description: 'Invalid argument')]
                    #[OA\Response(response: 500,description: 'Internal server error')]
                    
                    public function updateOrderStatus(Request $request) : Response
                    {
                        try{
                            $data = json_decode($request->getContent(), true);
                            
                            // Validate data
                            if (!isset($data['status']) && !isset($data['orderId'])) {
                                return $this->createResponse('Both Order ID and status must be provided.');
                            }
                            
                            if (isset($data['orderId']) && !is_numeric($data['orderId'])) {
                                return $this->createResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'Order ID must be integer.');
                            }
                            
                            if(isset($data['status'])){
                                if (!is_string($data['status'])) {
                                    return $this->createResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'Status must be a string.');
                                }
                                
                                if ($data['status'] != Order::$status_processing && $data['status'] != Order::$status_delivered) {
                                    return $this->createResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'The status can be either pending or delivered');
                                }
                            }
                            
                            $orderRepository = $this->entityManager->getRepository(Order::class);
                            $order = $orderRepository->find($data['orderId']);
                            
                            if (!$order) {
                                return $this->createResponse(Response::HTTP_NOT_FOUND, 'Order not found');
                            }
                            
                            $order->setStatus($data['status']);
                            $this->entityManager->flush();
                            
                            return $this->createResponse(Response::HTTP_OK, 'Order status updated successfully');
                        } catch (\Throwable $e) {
                            return $this->createResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Failed to update order status');
                        }
                    }
                }
                