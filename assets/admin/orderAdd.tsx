import "./styles/Admin-orderInfo.module.scss";

import {
  Col,
  DatePicker,
  Form,
  Input,
  Radio,
  Row,
  Switch,
  TimePicker,
  Typography,
  notification,
} from "antd";
import React, { useContext, useEffect, useState } from "react";

import { LayoutContext } from "./components/admin-layouts";
// import { DatePicker, Radio, Select, Switch } from '@formily/antd-components';
// import { Form, FormItem, Submit } from '@formily/antd';
import MenuAddDesk2 from "./components/menu_add_desk2";
import { useHistory } from "react-router";
import { useRequest } from "ahooks";

interface dataList {
  label: string;
  value: any;
  check: boolean;
  component: object;
  name: string;
  values: any;
  editShow: boolean;
  display: boolean;
}

const FormItem = Form.Item;
// @ts-ignore
export default function OrderAdd({ form }: { form?: any }) {
  const router = useHistory();
  const [orderInfoData, setorderInfoData] = useState({});
  const [MenuList, setMenuList] = useState([]);
  const defalitList: dataList[] = [];
  const [allPrice, setAllPrice] = useState("0.00");
  const [ListData, setListData] = useState(defalitList);
  const [ShowTake, setShowTake] = useState(false);
  const { IsMobile } = useContext(LayoutContext);

  const [AllDesk, setAllDesk] = useState<any[]>([]);

  const getAllDesk = useRequest("/api/admin/desk/all_desk/0", {
    manual: true,
    onSuccess(data) {
      const list: {
        label: any;
        value: any;
      }[] = [];

      for (let i in data.data) {
        let temp = data.data[i];
        list.push({
          label: temp.desk_name,
          value: temp.id,
        });
      }
      sessionStorage.setItem("desk_4", JSON.stringify(list));
      // @ts-ignore
      setAllDesk([].concat(list));
    },
  });

  const sendOrder = useRequest(
    (orderInfo: any) => ({
      method: "POST",
      url: `/api/admin/addTakewayOrder`,
      body: JSON.stringify(orderInfo),
    }),
    {
      manual: true,
      onSuccess: (data: any) => {
        notification.success({
          message: "提交成功",
          duration: 1.5,
        });
        setAllPrice("0.00");
        setMenuList([]);
        form.resetFields();
        router.push(data.links);
      },
      onError: () =>
        notification.error({
          message: "提交失败",
        }),
    },
  );

  useEffect(() => {
    const deskInfo = sessionStorage.getItem("desk_4");
    if (deskInfo) {
      setAllDesk([...JSON.parse(deskInfo || "[]")]);
    } else {
      getAllDesk.run();
    }
    setListData([
      {
        display: true,
        component: Input,
        name: "address",
        values: [],
        editShow: true,
        label: "配送地址信息",
        value: "",
        check: true,
      },
      {
        display: true,
        component: Input,
        name: "realname",
        values: [],
        editShow: true,
        label: "下单人姓名",
        value: "",
        check: true,
      },
      {
        display: true,
        component: Input,
        name: "phone",
        values: [],
        editShow: true,
        label: "下单手机号码",
        value: "",
        check: true,
      },
      {
        display: true,
        component: Radio.Group,
        name: "is_vat_exempt",
        values: [
          // @ts-ignore
          { label: "是", value: 1 },
          // @ts-ignore
          { label: "否", value: 0 },
        ],
        editShow: true,
        label: "是否免增值税",
        value: "",
        check: true,
      },
      {
        display: true,
        component: TimePicker,
        name: "delivery_order_date",
        values: "h:mm a",
        editShow: true,
        label: "预计取餐（或送达）时间",
        value: "",
        check: true,
      },
      {
        display: true,
        component: Radio.Group,
        name: "is_delivery",
        values: [
          // @ts-ignore
          { label: "送餐", value: 1 },
          // @ts-ignore
          { label: "自取", value: 0 },
        ],
        editShow: true,
        label: "自取还是送餐",
        value: "",
        check: true,
      },
    ]);
  }, []);

  return (
    <div className="orderInfo">
      <div className="orderInfoList">
        <div className="orderTables">
          <div className="orderTitle">
            <Typography.Title className="title">新建订单</Typography.Title>
          </div>
          <Form
            form={form}
            onFinish={(values) => {
              if (values.delivery_order_date_time) {
                values.delivery_order_date_time =
                  values.delivery_order_date_time[0].format("A h:mm") +
                  " - " +
                  values.delivery_order_date_time[1].format("A h:mm");
              }
              sendOrder.run({
                ...values,
                menu_order: MenuList,
                all_price: allPrice,
              });
            }}
          >
            <div
              className="orderThead"
              style={
                IsMobile
                  ? { width: "100%" }
                  : { width: "100%", margin: "20px auto" }
              }
            >
              <Row className="orderItem" style={{ border: 0 }}>
                <Col
                  span={IsMobile ? 8 : 4}
                  className="itemTitle"
                  style={{ borderRight: 0, textAlign: "right" }}
                ></Col>
                <Col span={IsMobile ? 16 : 20} className="itemContent">
                  <Radio.Group
                    onChange={(v) => {
                      if (v.target.value == 1) {
                        setShowTake(true);
                      } else {
                        setShowTake(false);
                      }
                    }}
                    buttonStyle="solid"
                    size="large"
                    defaultValue="0"
                  >
                    <Radio.Button defaultChecked value="0">
                      餐桌
                    </Radio.Button>
                    <Radio.Button value="1">外卖</Radio.Button>
                  </Radio.Group>
                </Col>
              </Row>
              <div className="orderItem" style={{ border: 0 }}>
                <div
                  className="itemTitle"
                  style={{ borderRight: 0, textAlign: "right" }}
                >
                  桌位
                </div>
                <div className="itemContent">
                  <FormItem
                    style={{ marginBottom: 0 }}
                    name="desk_id"
                    initialValue={0}
                  >
                    {/* <Select>
                                            {[
                                                { value: 0, label: '请选择' },
                                                ...AllDesk
                                            ].map((data: any, key: number) => (<Select.Option value={data.value} key={key}>{data.label}</Select.Option>))}
                                        </Select> */}
                    <Radio.Group
                      optionType="button"
                      buttonStyle="solid"
                      size="large"
                      options={[{ value: 0, label: "请选择" }, ...AllDesk]}
                    ></Radio.Group>
                  </FormItem>
                </div>
              </div>
              {ShowTake &&
                ListData.map((data: any, key: number) => (
                  <React.Fragment key={key}>
                    <Row className="orderItem" style={{ border: 0 }}>
                      <Col
                        span={IsMobile ? 8 : 4}
                        className="itemTitle"
                        style={{
                          borderRight: 0,
                          textAlign: "right",
                          padding: IsMobile ? "12px" : "0",
                        }}
                      >
                        {data.label}
                      </Col>
                      <Col span={IsMobile ? 16 : 20} className="itemContent">
                        {data.name == "delivery_order_date" ? (
                          <Row style={{ padding: 0, margin: 0 }}>
                            <Col span="8">
                              <FormItem
                                style={{ marginBottom: 0 }}
                                name="delivery_order_date"
                              >
                                <DatePicker
                                  placeholder="送达时间年月日（不选则默认当前）"
                                  format={"YYYY-MM-DD"}
                                ></DatePicker>
                              </FormItem>
                            </Col>
                            <Col span="16">
                              <FormItem
                                style={{ marginBottom: 0 }}
                                name="delivery_order_date_time"
                              >
                                <TimePicker.RangePicker
                                  placeholder={["最早送达", "最晚送达"]}
                                  format={"h:mm a"}
                                ></TimePicker.RangePicker>
                              </FormItem>
                            </Col>
                          </Row>
                        ) : (
                          <FormItem
                            style={{ marginBottom: 0 }}
                            name={data.name}
                          >
                            {data.component === Radio.Group && (
                              <Switch
                                unCheckedChildren="否"
                                checkedChildren="是"
                              />
                            )}
                            {data.component === Input && <Input />}
                            {data.component === Input.TextArea && (
                              <Input.TextArea />
                            )}
                          </FormItem>
                        )}
                      </Col>
                    </Row>
                  </React.Fragment>
                ))}
              <Row className="orderItem" style={{ border: 0 }}>
                <Col
                  span={IsMobile ? 8 : 4}
                  className="itemTitle"
                  style={{
                    borderRight: 0,
                    textAlign: "right",
                    padding: IsMobile ? "0" : "12px",
                  }}
                >
                  订单备注
                </Col>
                <Col span={IsMobile ? 16 : 20} className="itemContent">
                  <FormItem style={{ marginBottom: 0 }} name={"note"}>
                    <Input.TextArea />
                  </FormItem>
                </Col>
              </Row>
            </div>

            <MenuAddDesk2
              allprice={allPrice}
              isMobile={IsMobile}
              menuList={MenuList}
              onChange={(value: any, price: any) => setMenuList(value)}
              onChangePrice={(price: any) => setAllPrice(price)}
            />
            <div style={{ textAlign: "center" }}>
              {/* <Button type="primary" htmlType="submit" size="large">创建订单</Button> */}
            </div>
          </Form>
        </div>
      </div>
    </div>
  );
}

// ReactDOM.render(<CreateRouter>
//     <OrderInfo />
// </CreateRouter>, document.querySelector("#admin"));
