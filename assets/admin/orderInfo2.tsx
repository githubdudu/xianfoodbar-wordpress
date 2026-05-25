import './styles/Admin-orderInfo.module.sass';

import { useRequest } from 'ahooks';
import { Breadcrumb, Button, Col, Row, Table, Typography, notification } from 'antd';
import React, { useEffect, useState } from 'react'
import { useHistory, useLocation } from 'react-router';

import MenuAddDesk from './components/menu_add_desk';
import OrderEdit from './components/orderEdit';
import Steper from './components/SteperAnt';

interface Tests {
    oid: any,
    odid: any,
    data: any
}

export default function OrderInfo2() {
    const router = useHistory()
    const param = new URLSearchParams(useLocation().search);
    const [AddCount2, setAddCount2] = useState(true)
    const [EditOrder, setEditOrder] = useState(false)
    const [EditMenu, setEditMenu] = useState(false)
    const types: Tests[] = [];
    const [AddList, setAddList] = useState({})
    const [orderInfoData, setOrderInfoData] = useState({
        order: {
            order_sn: '',
            oid: '',
            order_status: 0,
            is_cancel: 0,
            is_delete: 0,
            create_time: '',
            pay_price: '',
            desk: {
                desk_name: '',
            },
            is_pin: 0,
            is_takeway: 0,
            key: "",
            note: "",
            pay_time: null,
            phone: "",
            pin_num: "",
            realname: "",
            takeway_order: "",
            user_id: 0,
            address: "",
            is_checked: 0
        },
        detail: [],
    });

    const column = [
        {
            title: "ID",
            dataIndex: "menu_id",
            align: 'center',
            key: "menu_id",
            render: (text: any, recore: any) => text.menu_num
        },
        {
            title: "订单ID",
            dataIndex: "oid",
            align: 'center',
            key: "oid",
        },
        {
            title: "菜单名",
            dataIndex: "menu_name",
            key: "menu_name",
        },
        {
            title: "备注",
            dataIndex: "note",
            key: "note",
            render: (text: any) => <pre style={{ whiteSpace: 'pre' }}>{text}</pre>
        },
        {
            title: "数量",
            dataIndex: "total",
            key: "total",
        },
        {
            title: '状态',
            key: 'status',
            render: (text: any, recore: any) => (<div>{recore.is_delete == 1 ? <Button type="primary" danger size="small">已删除</Button> : <Button size="small" style={{ borderColor: '#54d08e', backgroundColor: '#54d08e' }} type="primary">正&nbsp;&nbsp;&nbsp;&nbsp;常</Button>}</div>)
        },
        {
            title: "已上菜",
            dataIndex: "add_count",
            align: 'center',
            key: "add_count",
            render: (value: number, recore: any) => {
                let tempData = 0;
                if (AddCount2) {
                    return (
                        <div className="addCountBtn" style={{ width: 270, margin: '0 auto' }}>
                            <Steper style={{ display: 'inline-block', marginRight: 10 }} max={recore.total} defaultValue={value} value={value} onChange={(v: any) => {
                                setAddCount.run(recore.odid, recore.oid, v);
                            }} />

                            {/* <Button onClick={() => {
                                setAddCount.run(recore.odid, recore.oid, tempData);
                                setAddCount2(false);
                                tempData = 0;
                            }} style={{ display: 'inline-block', marginRight: 10 }} className="successBtn" type="primary" >确认</Button>
                            <Button style={{ display: 'inline-block' }} className="cancelBtn" onClick={() => {
                                setAddCount2(false)
                                tempData = 0;
                            }} type="primary" danger >取消</Button> */}
                        </div >
                    );
                } else {
                    return value;
                }
            }
        },
        {
            title: '操作',
            key: 'options',
            render: (text: any, recore: any) => (
                <React.Fragment>
                    <Button onClick={() => deleteOd.run(recore.oid, recore.odid)} type="primary" danger={recore.is_delete != 1} >{recore.is_delete != 1 ? "删除" : "恢复"}</Button>
                </React.Fragment>
            )
        }
    ]

    const deleteOd = useRequest((oid = 0, odid = 0) => `/api/user/order_item/delete/${oid}/${odid}`, {
        manual: true,
        onSuccess: () => (notification.success({
            message: '提示',
            description: '操作成功'
        }), infoData.refresh()),
        onError: (e) => notification.error({
            message: '提示',
            description: e.message || '删除失败'
        })
    })

    const infoData = useRequest((pid) => `/api/admin/order_detail/${pid}`, {
        manual: true,
        onSuccess(data) {
            setOrderInfoData({
                order: data.data.order,
                detail: data.data.detail
            });
        },
        onError() {
            notification.error({
                message: '错误',
                description: '未知订单号'
            });
        },
    });

    const setAddCount = useRequest(
        (odid, oid, total) => ({
            url: `/api/user/update-count/${odid}`,
            method: "POST",
            body: JSON.stringify({
                add_count: total,
                oid
            }),
        }),
        {
            manual: true,
            onSuccess() {
                infoData.refresh();
                notification.success({
                    message: '提醒',
                    description: '上菜成功',

                });
            },
            onError() {
                notification.error({
                    message: '错误',
                    description: '修改失败'
                });
            },
        },
    );



    useEffect(() => {
        // @ts-ignore
        if (param == {}) {
            window.location.href = ("/orderList");
        }
        // @ts-ignore
        infoData.run(param.get('id'));
    }, []);

    return (
        <div className="orderInfo">
            <div className="orderInfoList">
                <div className="orderTables">
                    <div className="orderTitle">
                        <Breadcrumb separator="">
                            <Breadcrumb.Item>快速跳转</Breadcrumb.Item>
                            <Breadcrumb.Separator>:</Breadcrumb.Separator>
                            <Breadcrumb.Item className="cursor" onClick={() => window.location.href = ("")}>首页</Breadcrumb.Item>
                            <Breadcrumb.Separator />
                            <Breadcrumb.Item className="cursor" onClick={() => window.location.href = ("/adminpanel/system/tabs/OrderSystem/list" + (orderInfoData.order.is_takeway == 0 ? "?activeKey=desk" : '?activeKey=takeway'))}>返回{orderInfoData.order.is_takeway == 0 ? '餐厅' : '外卖'}</Breadcrumb.Item>
                            <Breadcrumb.Separator />
                            <Breadcrumb.Item>订单详情</Breadcrumb.Item>
                        </Breadcrumb>,
                            <Typography.Title className="title">
                            ID: {orderInfoData.order.oid} 订单详情 <Button type="primary" size="small" style={{ verticalAlign: 'top', marginLeft: '15px' }} danger={EditOrder} onClick={() => setEditOrder(!EditOrder)}>{EditOrder ? "取消编辑" : "编辑订单"}</Button>
                        </Typography.Title>
                    </div>
                    
                    {EditMenu &&
                        <MenuAddDesk oid={orderInfoData.order.oid} isEdit={true} orderList={orderInfoData.detail} EditSuccess={(data: any) => {
                            notification.success({
                                message: '修改成功'
                            });
                            router.push(data.links);
                            // infoData.refresh();
                            setEditMenu(false);
                        }} EditError={() => notification.error({ message: '提交失败', description: '发生一些错误, 请稍后重试' })} hideBtn={() => setEditMenu(false)} />}
                    {!EditMenu &&
                        <div className="orderTbody">
                            <div className="tableTitle">
                                <Row>
                                    <Col span="2" style={{ lineHeight: '32px' }}>菜单数据</Col>
                                    <Col span="22">
                                        <div className="btns" style={{ textAlign: 'left' }}>
                                        </div>
                                    </Col>
                                </Row>


                            </div>
                            <Table pagination={
                                {
                                    position: ["bottomCenter"]
                                }
                            } bordered={true} className="Tables"
                                // @ts-ignore
                                columns={column} dataSource={orderInfoData.detail} pagination={false} footer={() => <Button type="primary" onClick={() => setEditMenu(true)}>点此加菜</Button>}>
                            </Table>
                        </div>}

                        <OrderEdit orders={orderInfoData.order} infoData={infoData} edit={EditOrder} success={() => setEditOrder(!EditOrder)} />
                </div>
            </div>

        </div>
    );
}