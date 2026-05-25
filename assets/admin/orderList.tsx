import './styles/Admin-orderList.module.sass';
import '../bootstrap';

import { Col, Row, Tabs } from 'antd';
import React, { useEffect, useState } from 'react';

import AdminLayout from './components/admin-layouts';
import ReactDOM from 'react-dom'
import { useRequest } from 'ahooks';

function OrderList() {
  const [OrderList, setOrderList] = useState([])
  const [ActiveKey, setActiveKey] = useState("0")

  const getList = useRequest(
    (takeyway = 0) => `/api/user/all_order/${takeyway}`,
    {
      manual: true,
      debounceInterval: 500,
      pollingInterval: 500,
      onSuccess: (data) => (data.data && setOrderList(data.data)),
    },
  );

  useEffect(() => {
    getList.run()
  }, []);

  useEffect(() => {
    // @ts-ignore
    setActiveKey(param.query.type);
    // @ts-ignore
    getList.run(param.query.type)
  },
    // @ts-ignore
    [param.query])

  return (

    <AdminLayout>
      <div className="orderList">
        <Tabs centered activeKey={ActiveKey} style={{ marginBottom: 50 }} onTabClick={(key) => (getList.run(key), setActiveKey(key))}>
          <Tabs.TabPane tab={<span style={{ fontSize: 18 }}>餐厅</span>} key="0"></Tabs.TabPane>
          <Tabs.TabPane tab={<span style={{ fontSize: 18 }}>外卖</span>} key="1"></Tabs.TabPane>
        </Tabs>
        <Row gutter={[30, 30]}>
          {OrderList.map((data: any, key: number) => (
            <Col span={4} key={key}>
              {data.desk_info &&
                <div onClick={() => {
                  if (data.order_info && data.order_info.oid > 0) {
                    location.href = ('/orderInfo/' + data.order_info.oid);
                  } else {
                    location.href = ('/deskInfo/' + data.desk_info.did);
                  }
                }} className={"orderCol " + (data.order_info.oid > 0 && "orderActive")}>
                  <div className={"orderTitle"}>
                    {data.desk_info.desk_name}
                  </div>
                  {data.order_info.oid > 0 &&
                    <div className={"orderInfo"}>
                      {data.order_info.is_takeway == 1 && <div>后厨桌位:  <span style={{ fontWeight: 'bold', color: '#fff' }}>{data.desk_info.menu_guid}</span></div>}
                      <div>状&nbsp;&nbsp;&nbsp;&nbsp;态: {data.order_info.order_status == 0 ? <span style={{ fontWeight: 'bold', color: '#fff' }}>未支付</span> : <span style={{ fontWeight: 'bold', color: '#fff' }}>已支付</span>}</div>
                      <div>点菜数: <span style={{ fontWeight: 'bold', color: '#fff' }}>{data.all_count}</span></div>
                      <div>未上菜:  <span style={{ fontWeight: 'bold', color: '#fff' }}>{data.last_order}</span></div>
                    </div>
                  }
                </div>
              }
            </Col>
          ))}
        </Row>
      </div >
    </AdminLayout>

  )
}

ReactDOM.render(<OrderList />, document.getElementById('admin'));
