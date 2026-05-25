import { useRequest } from 'ahooks';
import { Button, Input, Table, notification } from 'antd'
import React, { useCallback, useEffect, useState } from 'react'

import AddCountBtns from './addcount-btns';
import Steper from './SteperAnt';

export default function MenuAddDesk(props: {
  orderList?: any[],
  oid?: any,
  isEdit?: boolean,
  hideBtn?: any,
  EditSuccess?: any,
  EditError?: any,
} = {
    orderList: [],
    oid: 0,
    isEdit: false,
    hideBtn: () => { },
    EditSuccess: (data: any) => { },
    EditError: (data: any) => { },
  }) {
  const ListTableValue: any[] = [];
  const [ListTable, setListTable] = useState(ListTableValue)
  const [allPrice, setAllPrice] = useState("0.00");
  const [DeskList, setDeskList] = useState([])

  const column = [
    {
      title: "菜单ID",
      dataIndex: "mid",
      key: "mid",
    },
    {
      title: "菜单名",
      dataIndex: "name",
      key: "name",
    },
    {
      title: "单价",
      dataIndex: "price",
      key: "price",
      render: (value: any, recore: any, index: number) => (parseFloat(value).toFixed(2))
    },
    {
      title: "数量",
      dataIndex: "total",
      key: "total",
      render: (value: any, recore: any, index: number) => <Steper onChange={(value: any) => {
        recore.total = value;
        ListTable[index] = recore;
        setListTable(ListTable);
        updatePrice(ListTable);
      }} value={value} defaultValue={value} />
    },
    {
      title: "备注",
      dataIndex: "note",
      key: "note",
      render: (value: any, recore: any, index: number) => <Input.TextArea onChange={(e) => {
        recore.note = e.target.value;
        ListTable[index] = recore;
        setListTable(ListTable);
      }} rows={5} defaultValue={value} />
    },
    {
      title: '操作',
      key: 'options',
      render: (text: any, recore: any, index: number) => <Button onClick={() => {
        recore.is_delete = !recore.is_delete;
        ListTable[index] = recore;
        setListTable(ListTable);
        updatePrice(ListTable)
      }} type="primary" danger={recore.is_delete === false} >{recore.is_delete ? '恢复' : '删除'}</Button>
    }
  ]

  const getTakeDeskList = useRequest((type = 1) => `/api/admin/desk/all_desk/${type}`, {
    manual: true,
    onSuccess: (data) => {
      if (data.data) {
        const list: {
          label: any,
          value: any,
        }[] = [];

        for (let i in data.data) {
          let temp = data.data[i];
          list.push({
            label: temp.desk_name,
            value: temp.id,
          })
        }
        sessionStorage.setItem('desk_2', JSON.stringify(list));
        //  @ts-ignore
        setDeskList(list);
      }
    },
    onError: () => notification.error({
      message: '请求错误'
    })
  });

  const updatePrice = useCallback((list_data: any) => {
    var now = 0.0;
    list_data.forEach((data: any) => {
      if (data.is_delete === false) {
        // @ts-ignore
        now = parseFloat(now) + parseFloat(data.price) * parseInt(data.total);
      }
    });
    // @ts-ignore
    setAllPrice(parseFloat(now).toFixed(2));
  }, []);

  const EditPost = useRequest((oid: number = 0, all_price = 0, orderData = {}) => ({
    url: `/api/admin/orderAddMenu/${oid}`,
    method: 'POST',
    body: JSON.stringify({
      menus: orderData,
      all_price,
    })
  }), {
    manual: true,
    onSuccess: (data) => props.EditSuccess(data),
    onError: (data) => props.EditError(data),
  })

  const insertData = useCallback((info: any) => {

    let now = null;
    for (let i in ListTable) {
      let temp = ListTable[i];
      // @ts-ignore
      if (info.mid == temp.mid) {
        // @ts-ignore
        now = i;
        break;
      }
    }

    if (now != null) {
      // @ts-ignore
      let temp = ListTable[now];
      // @ts-ignore
      temp.total += 1;
      // @ts-ignore
      ListTable[now] = temp;
      // console.log(ListTable.list.list);
    } else {
      ListTable.push({
        price: info.menu_price,
        num: info.menu_num,
        name: info.menu_name,
        key: ListTable.length,
        mid: info.mid,
        total: 1,
        odid: 0,
        is_delete: false,
        note: "",
      });
    }

    setListTable(ListTable);
    updatePrice(ListTable);
  }, [ListTable])

  const UpdateInit = useCallback((orderList: any) => {
    if (orderList && orderList.length > 0) {
      orderList.map((data: any) => {
        ListTable.push({
          price: data.menu_id.menu_price,
          num: data.menu_id.total,
          name: data.menu_id.menu_name,
          key: ListTable.length,
          mid: data.menu_id.mid,
          total: data.total,
          odid: data.odid,
          is_delete: data.is_delete === 1,
          note: data.note,
        });
      })
      setListTable(ListTable);
      updatePrice(ListTable);
    }
  }, [ListTable])

  useEffect(() => {
    const deskList2 = sessionStorage.getItem('desk_2');
    if (deskList2) {
      setDeskList(JSON.parse(deskList2));
    } else {
      getTakeDeskList.run()
    }
    UpdateInit(props.orderList);
  }, [props.orderList])

  return (
    <div className="menu_add_desk" style={{ margin: '30px 0', padding: '20px', border: '1px solid #eee' }}>
      <div className="title">
        <h4 style={{ color: '#747474' }}>修改菜单列表</h4>
      </div>
      <div className="addMenu" style={{ position: 'fixed', bottom: 50, right: 50, width: 400, padding: 20, border: '1px solid #000', zIndex: 99999, background: '#fff', margin: '0 auto' }}>
        <AddCountBtns onChange={(values: any) => insertData(values.data)} />
      </div>

      <Table pagination={false} bordered columns={column}
        // @ts-ignore
        dataSource={[].concat(ListTable)} footer={(s) => <div style={{ textAlign: 'right', fontSize: 16, fontWeight: 'bold', color: '#db5d5d' }}>总金额: {allPrice}元 <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> {props.isEdit && <><Button type="primary" onClick={() => EditPost.run(props.oid, allPrice, ListTable)}>提交</Button> <Button type="primary" danger onClick={props.hideBtn}>关闭</Button> </>}</div>} />
    </div>
  );
}
