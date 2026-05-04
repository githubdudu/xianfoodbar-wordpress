<?php

namespace App\Model;

use App\Entity\Order;
use App\Entity\OrderDetail;
use App\ORM\Model;
use App\ORM\Query\Pagination;

/**
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(string|array $name, string|int|null $value = null,  ?array $orderBy = null)
 * @method Order[]    findAll(?array $orderBy = null)
 * @method Order[]    findBy(string|array $name, string|int|null $value = null,  ?array $orderBy = null)
 */
class OrderModel extends Model
{
    public function __construct()
    {
        parent::__construct(Order::class);
    }

    public function getYesterdayCount(string $yesterday, string $now)
    {
        $yesterday = date('Y-m-d', $yesterday);
        $now       = date('Y-m-d', $now);
        return $this->createQueryBuilder('o')
            ->select('COUNT(o.oid)')
            ->where('o.confirm_time', 'between', [$yesterday, $now])
            ->andWhere('o.order_status', 2)
            ->andWhere('o.is_delete', 0)
            ->andWhere('o.is_cancel', 0)
            ->getPagination()
            ->getTotal();
    }

    public function getYesterdayMoney(string $yesterday, string $now)
    {
        $yesterday = date('Y-m-d', $yesterday);
        $now       = date('Y-m-d', $now);
        return $this->createQueryBuilder('o')
            ->select('SUM(o.pay_price)')
            ->where('o.confirm_time', 'between', [$yesterday, $now])
            ->andWhere('o.order_status', 2)
            ->andWhere('o.is_delete', 0)
            ->andWhere('o.is_cancel', 0)
            ->getQuery()
            ->noCache()
            ->getVariable();
    }

    public function getTodayCount(string $tomorrow, string $now)
    {
        $tomorrow = date('Y-m-d', $tomorrow);
        $now      = date('Y-m-d', $now);
        return $this->createQueryBuilder('o')
            ->select('COUNT(o.oid)')
            ->where('o.confirm_time', 'between', [$now, $tomorrow])
            ->andWhere('o.order_status', 2)
            ->andWhere('o.is_delete', 0)
            ->andWhere('o.is_cancel', 0)
            ->getPagination()
            ->getTotal();
    }

    public function getTodayDeskMoney(int $desk_id, string $tomorrow, string $now)
    {
        return $this->createQueryBuilder('o')
            ->select('SUM(o.pay_price)')
            ->where('o.confirm_time', 'between', [date('Y-m-d', $now), date('Y-m-d', $tomorrow)])
            ->andWhere('o.order_status', 2)
            ->andWhere('o.desk_id', $desk_id)
            ->andWhere('o.is_delete', 0)
            ->andWhere('o.is_cancel', 0)
            ->getQuery()
            ->noCache()
            ->getVariable();
    }

    public function getTodayPayTypeMoney(int $pay_type, string $tomorrow, string $now)
    {
        return $this->createQueryBuilder('o')
            ->select('SUM(o.pay_price)')
            ->where('o.confirm_time', 'between', [date('Y-m-d', $now), date('Y-m-d', $tomorrow)])
            ->andWhere('o.order_status', 2)
            ->andWhere('o.pay_type', $pay_type)
            ->andWhere('o.is_delete', 0)
            ->andWhere('o.is_cancel', 0)
            ->getQuery()
            ->noCache()
            ->getVariable();
    }

    public function getTodayMoney(string $tomorrow, string $now)
    {
        return $this->createQueryBuilder('o')
            ->select('SUM(o.pay_price)')
            ->where('o.confirm_time', 'between', [date('Y-m-d', $now), date('Y-m-d', $tomorrow)])
            ->andWhere('o.order_status', 2)
            ->andWhere('o.is_delete', 0)
            ->andWhere('o.is_cancel', 0)
            ->getQuery()
            ->noCache()
            ->getVariable();
    }

    public function getMoneyFormDay(string | int $startTime, string | int $endTime)
    {
        return $this->createQueryBuilder()
            ->select('SUM(pay_price)')
            ->where('confirm_time', 'between', [date('Y-m-d', $startTime), date('Y-m-d', $endTime)])
            ->andWhere('order_status', 2)
            ->andWhere('is_delete', 0)
            ->andWhere('is_cancel', 0)
            ->getQuery()
            ->noCache()
            ->getVariable();
    }

    public function getIsDeskMoneyFormDay(string | int $startTime, string | int $endTime)
    {
        return $this->createQueryBuilder()
            ->select('SUM(pay_price)')
            ->where('confirm_time', 'between', [date('Y-m-d', $startTime), date('Y-m-d', $endTime)])
            ->andWhere('order_status', 2)
            ->andWhere('is_delete', 0)
            ->andWhere('is_cancel', 0)
            ->andWhere('is_takeway', 0)
            ->getQuery()
            ->noCache()
            ->getVariable();
    }

    public function getIsTakeWayMoneyFormDay(string | int $startTime, string | int $endTime)
    {
        return $this->createQueryBuilder()
            ->select('SUM(pay_price)')
            ->where('confirm_time', 'between', [date('Y-m-d', $startTime), date('Y-m-d', $endTime)])
            ->andWhere('order_status', 2)
            ->andWhere('is_delete', 0)
            ->andWhere('is_cancel', 0)
            ->andWhere('is_takeway', 1)
            ->getQuery()
            ->noCache()
            ->getVariable();
    }

    /**
     * Undocumented function
     *
     * @return Order[]|null
     */
    public function getOrderDeskInfo()
    {
        return $this->createQueryBuilder()
            ->select('desk_id', 'pin_num', 'is_pin', 'is_takeway', 'create_time', 'note')
            ->where([
                ['order_status', '<', 2],
                'is_delete'  => 0,
                'is_cancel'  => 0,
                'is_checked' => 1,
            ])
            ->orderBy('is_takeway', 'asc')
            ->orderBy('create_time', 'asc')
            ->getQuery()
            ->getResults();
    }

    public function getIndexOrderByOrderSn()
    {

    }

    public function getAllByWhere(array $where = []): array | null
    {
        $query = $this->createQueryBuilder()
            ->orderBy('create_time', 'DESC');

        if (!empty($where)) {
            $query->where($where);
        }

        return $query->getQuery()->getResults();
    }

    public function getAllInfo(array $where = []): Pagination
    {
        $query = $this->createQueryBuilder()
            ->orderBy('create_time', 'DESC');

        if (!empty($where)) {
            $query->where($where);
        }

        return $query->getPagination();
    }

    public function getAll()
    {
        return $this->createQueryBuilder('o')
            ->innerJoin(OrderDetail::class, 'od', 'od.oid = o.oid')
            ->where('o.oid', '>', 0)
            ->getQuery()
            ->noCache()
            ->getResults();
    }
}
