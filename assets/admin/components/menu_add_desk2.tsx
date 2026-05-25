import { useRequest } from "ahooks";
import { Button, Col, Row, Table, notification } from "antd";
import React, { useCallback, useEffect, useState } from "react";

import AddCountBtns from "./addcount-btns";
import Steper from "./SteperAnt";

export default function MenuAddDesk2(
  props: {
    hideBtn?: any;
    onChange?: any;
    onChangePrice?: any;
    isMobile: boolean;
    menuList?: any;
    allprice?: any;
  } = {
    hideBtn: () => {},
    onChange: (data: any) => {},
    onChangePrice: (data: any) => {},
    isMobile: false,
  },
) {
  const ListTableValue: any[] = [];
  const [ListTable, setListTable] = useState(ListTableValue);
  const [allPrice, setAllPrice] = useState("0.00");
  const [DeskList, setDeskList] = useState([]);

  const column = [
    {
      title: "菜单名",
      dataIndex: "name",
      key: "name",
    },
    {
      title: "单价",
      dataIndex: "price",
      key: "price",
      render: (value: any, recore: any, index: number) =>
        parseFloat(value).toFixed(2),
    },
    {
      title: "数量",
      dataIndex: "total",
      key: "total",
      render: (value: any, recore: any, index: number) => (
        <Steper
          onChange={(value: any) => {
            recore.total = value;
            ListTable[index] = recore;
            setListTable(ListTable);
            updatePrice(ListTable);
          }}
          value={value}
          defaultValue={value}
        />
      ),
    },
    {
      title: "操作",
      key: "options",
      render: (text: any, recore: any, index: number) => (
        <Button
          onClick={() => {
            // recore.is_delete = !recore.is_delete;
            ListTable.splice(index, 1);
            setListTable(ListTable);
            updatePrice(ListTable);
          }}
          type="primary"
          danger={recore.is_delete === false}
        >
          {recore.is_delete ? "恢复" : "删除"}
        </Button>
      ),
    },
  ];

  const getTakeDeskList = useRequest(
    (type = 1) => `/api/admin/desk/all_desk/${type}`,
    {
      manual: true,
      onSuccess: (data) => {
        if (data.data) {
          sessionStorage.setItem(`desk_1`, JSON.stringify(data.data));
          //  @ts-ignore
          setDeskList(data.data);
        }
      },
      onError: () =>
        notification.error({
          message: "请求错误",
        }),
    },
  );

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
    // @ts-ignore
    props.onChangePrice(parseFloat(now).toFixed(2));
  }, []);

  const insertData = useCallback(
    (info: any) => {
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

      if (now !== null) {
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
      props.onChange(ListTable, allPrice);
    },
    [ListTable],
  );

  useEffect(() => {
    const deskInfo2 = sessionStorage.getItem(`desk_1`);
    if (deskInfo2) {
      setDeskList(JSON.parse(deskInfo2));
    } else {
      getTakeDeskList.run();
    }
    console.log(props.isMobile);
  }, []);

  useEffect(() => {
    setListTable(props.menuList);
    setAllPrice(props.allprice);
  }, [props.menuList]);

  return (
    <div
      className="menu_add_desk"
      style={
        props.isMobile
          ? {
              width: "100%",
              margin: "14px auto",
              padding: "4px",
              border: "1px solid #eee",
            }
          : {
              width: "100%",
              margin: "14px auto",
              padding: "10px",
              border: "1px solid #eee",
            }
      }
    >
      <div className="title">
        <h4 style={{ color: "#747474" }}>菜单列表</h4>
      </div>
      <div className="menu_add_list">
        <div className="menu_add_list_table" style={{ paddingTop: 60 }}>
          <Table
            pagination={false}
            bordered
            columns={column}
            // @ts-ignore
            dataSource={[].concat(ListTable)}
            footer={(s) => (
              <div
                style={{
                  textAlign: "right",
                  fontSize: 16,
                  fontWeight: "bold",
                  color: "#db5d5d",
                }}
              >
                总金额: {allPrice}元{" "}
                <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
              </div>
            )}
          />
        </div>
        <div className="menu_add_list_pad">
          <div className="addMenu" style={{ width: "100%", margin: "0 auto" }}>
            <AddCountBtns onChange={(values: any) => insertData(values.data)} />
          </div>
        </div>
      </div>
    </div>
  );
}
