import "../styles/Admin-orderInfo.module.scss";

import { useRequest } from "ahooks";
import {
  Button,
  Col,
  Form,
  Input,
  Modal,
  Radio,
  RadioChangeEvent,
  Row,
  Select,
  Switch,
  notification,
} from "antd";
import React, { useEffect, useState } from "react";

const FormItem = Form.Item;

type ListDataType = {
  label: string;
  value: any;
  check: boolean;
  component: any;
  name: string;
  values: any[];
  editShow: boolean;
  default?: any;
  display: boolean;
};

export default function OrderEdit(
  props: {
    orders: any;
    payInfo: any[];
    defaultPayInfo: any;
    infoData: {
      refresh: any;
    };
    showDiscount?: boolean;
    edit: boolean;
    success?: any;
    updateEdit: CallableFunction;
  } = {
    orders: {
      order_sn: "",
      oid: "",
      order_status: 0,
      is_cancel: 0,
      is_delete: 0,
      create_time: "",
      pay_price: "",
      pay_type: 0,
      desk: {
        desk_name: "",
      },
      is_pin: 0,
      is_takeway: 0,
      pay_discount: 0,
      key: "",
      note: "",
      pay_time: null,
      phone: "",
      pin_num: "",
      realname: "",
      takeway_order: "",
      user_id: 0,
      address: "",
      is_checked: 0,
    },
    infoData: {
      refresh: () => {},
    },
    payInfo: [],
    showDiscount: false,
    defaultPayInfo: 0,
    edit: false,
    updateEdit: (e: any) => {},
  },
) {
  const [orderInfoData, setorderInfoData] = useState({} as any);
  const [FormData, setFormData] = useState({} as any);
  const [isEdit, setIsEdit] = useState(props.edit);
  const [AllDesk, setAllDesk] = useState([]);
  const [discountData, setDiscountData] = useState(props.orders?.pay_discount);
  const [oldDefaultFormData, setOldDefaultFormData] = useState({} as any);
  const [form] = Form.useForm();

  useEffect(() => {
    setIsEdit(props.edit);
  }, [props.edit]);

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

      sessionStorage.setItem("desk_3", JSON.stringify(list));
      // @ts-ignore
      setAllDesk([].concat(list));
    },
  });

  const [ListData, setListData] = useState<ListDataType[]>([
    {
      label: "",
      value: "",
      check: false,
      component: {},
      name: "",
      values: [],
      editShow: false,
      display: true,
    },
  ]);

  const checkedOrder = useRequest(
    (order_id) => `/api/user/order/checked/${order_id}`,
    {
      manual: true,
      onSuccess() {
        props.infoData.refresh();
      },
      onError() {
        notification.error({
          message: "错误",
          description: "确认失败，请稍后重试",
        });
      },
    },
  );

  const discountOrder = useRequest(
    (order_id: number = 0, discount: string = "") => ({
      url: `/api/order/discount/${order_id}`,
      method: "POST",
      body: JSON.stringify({
        discount: discount,
      }),
    }),
    {
      manual: true,
      onSuccess() {
        //setDiscountData('')
        props.infoData.refresh();
      },
      onError() {
        notification.error({
          message: "错误",
          description: "设置折扣失败，请稍后重试",
        });
      },
    },
  );

  const editOrder = useRequest(
    (order_id: number = 0, orderInfo = {}) => ({
      url: `/api/order/edit/${order_id}`,
      method: "POST",
      body: JSON.stringify(orderInfo),
    }),
    {
      manual: true,
      onSuccess() {
        props.success();
        notification.success({
          message: "修改成功",
        });
        props.infoData.refresh();
      },
      onError() {
        notification.error({
          message: "错误",
          description: "提交失败，请稍后重试",
        });
      },
    },
  );

  const payOrder = useRequest(
    (order_id, pay_type = 0) => ({
      url: `/api/order/pay/${order_id}`,
      method: "POST",
      body: JSON.stringify({
        pay_type: pay_type,
      }),
    }),
    {
      manual: true,
      onSuccess() {
        props.infoData.refresh();
      },
      onError() {
        notification.error({
          message: "错误",
          description: "支付失败，请稍后重试",
        });
      },
    },
  );
  const confirmOrder = useRequest(
    (order_id) => `/api/order/confirm/${order_id}`,
    {
      manual: true,
      onSuccess() {
        props.infoData.refresh();
      },
      onError() {
        notification.error({
          message: "错误",
          description: "确认完成失败，请稍后重试",
        });
      },
    },
  );
  const cancelOrder = useRequest(
    (order_id) => `/api/order/cancel/${order_id}`,
    {
      manual: true,
      onSuccess() {
        props.infoData.refresh();
      },
      onError() {
        notification.error({
          message: "错误",
          description: "取消失败，请稍后重试",
        });
      },
    },
  );

  const restOrder = useRequest(
    (order_id) => ({
      url: `/api/admin/order/changeStatus/${order_id}`,
      method: "post",
      body: JSON.stringify({
        status: 0,
      }),
    }),
    {
      manual: true,
      onSuccess() {
        props.infoData.refresh();
      },
      onError() {
        notification.error({
          message: "错误",
          description: "重置失败，请稍后重试",
        });
      },
    },
  );

  useEffect(() => {
    const config = sessionStorage.getItem("desk_3");
    if (config) {
      // @ts-ignore
      setAllDesk([].concat(JSON.parse(config || "[]")));
    } else {
      getAllDesk.run();
    }
  }, []);

  useEffect(() => {
    setorderInfoData(props.orders);
    const showDiscount = props.showDiscount;
    const price = parseFloat(props.orders.pay_price);
    const discount = showDiscount ? parseFloat(props.orders?.pay_discount) : 0;
    setListData([
      {
        display: true,
        component: "",
        name: "price",
        values: [],
        editShow: false,
        label: "订单金额",
        //@ts-ignore
        value: (
          <div style={{ fontSize: 25, color: "red", fontWeight: "bold" }}>
            {!props.showDiscount ? (
              <>{"$" + parseFloat(props.orders.pay_price).toFixed(2)}</>
            ) : (
              <>
                <span>
                  {"$" + (price - price * (discount / 100)).toFixed(2)}
                </span>
                {props.orders?.pay_discount > 0 && (
                  <span
                    style={{
                      fontSize: 18,
                      color: "#999",
                      textDecoration: "line-through",
                      paddingLeft: 10,
                    }}
                  >
                    {" "}
                    ${props.orders?.pay_price}
                  </span>
                )}
              </>
            )}
          </div>
        ),
        check: true,
      },
      {
        display: true,
        component: "",
        name: "status",
        values: [],
        editShow: false,
        label: "订单状态",
        //@ts-ignore
        value: (
          <>
            {props.orders.order_status == 0 && props.orders.is_cancel == 0 && (
              <span className="no_pay">未支付</span>
            )}
            {props.orders.order_status == 1 && props.orders.is_cancel == 0 && (
              <span className="no_confirm">已支付</span>
            )}
            {props.orders.order_status == 2 && props.orders.is_cancel == 0 && (
              <span className="confirm">已完成</span>
            )}
            {(props.orders.is_cancel == 1 || props.orders.is_delete == 1) && (
              <span className="no_pay">已取消</span>
            )}
          </>
        ),
        check: true,
      },
      {
        display: true,
        component: Radio.Group,
        name: "pay_type",
        value: (
          <>
            {props.payInfo.map((item, key) => (
              <React.Fragment key={key}>
                {props.orders.pay_type == item.value && (
                  <span className="no_pay">{item.label}</span>
                )}
              </React.Fragment>
            ))}
          </>
        ),
        values: props.payInfo,
        default: props.defaultPayInfo,
        editShow: true,
        label: "支付方式",
        check: true,
      },
      {
        display: true,
        component: Switch,
        name: "is_takeway",
        values: [
          // @ts-ignore
          { label: "是", value: 1 },
          // @ts-ignore
          { label: "否", value: 0 },
        ],
        editShow: true,
        label: "是否外卖",
        value: props.orders.is_takeway == 1 ? "是" : "否",
        check: true,
      },
      {
        display: true,
        component: Input.TextArea,
        name: "note",
        values: [],
        editShow: true,
        label: "订单备注",
        value: props.orders.note,
        check: true,
      },
      {
        display: true,
        component: "",
        name: "code",
        values: [],
        editShow: false,
        label: "订单编号",
        value: props.orders.order_sn,
        check: true,
      },
      {
        display: true,
        component: "",
        name: "date",
        values: [],
        editShow: false,
        label: "下单时间",
        value: props.orders.create_time,
        check: true,
      },
      {
        display: true,
        component: Select,
        name: "desk",
        values: [{ label: "请选择", value: 0 }, ...AllDesk],
        editShow: false,
        label: "桌号",
        value: props.orders.desk && props.orders.desk.desk_name,
        check: true,
      },

      {
        display: false,
        component: Select,
        name: "desk_id",
        values: [{ label: "请选择", value: 0 }, ...AllDesk],
        editShow: true,
        label: "桌号ID",
        value: props.orders.desk_id,
        check: true,
      },

      {
        display: true,
        component: "",
        name: "desk_2",
        values: [],
        editShow: false,
        label: "拼桌状态",
        value: props.orders.is_pin > 0 ? "拼桌" : "未拼桌或最先使用",
        check: true,
      },

      {
        display: true,
        component: Switch,
        name: "is_vat_exempt",
        values: [
          // @ts-ignore
          { label: "是", value: 1 },
          // @ts-ignore
          { label: "否", value: 0 },
        ],
        editShow: true,
        label: "是否免增值税",
        value: props.orders.is_vat_exempt == 1 ? "是" : "否",
        check: props.orders.is_takeway == 1,
      },
      {
        display: true,
        component: Switch,
        name: "is_delivery",
        values: [
          // @ts-ignore
          { label: "送餐", value: 1 },
          // @ts-ignore
          { label: "自取", value: 0 },
        ],
        editShow: true,
        label: "自取还是送餐",
        value: props.orders.is_delivery == 1 ? "送餐" : "自取",
        check: props.orders.is_takeway == 1,
      },
      {
        display: true,
        component: Input,
        name: "delivery_order_date",
        values: [],
        editShow: true,
        label: "预计取餐（或送达）时间",
        value: props.orders.delivery_order_date,
        check: props.orders.is_takeway == 1,
      },
      {
        display: true,
        component: Input,
        name: "address",
        values: [],
        editShow: true,
        label: "配送地址信息",
        value: props.orders.address,
        check: props.orders.is_takeway == 1,
      },
      {
        display: true,
        component: Input,
        name: "realname",
        values: [],
        editShow: true,
        label: "下单人姓名",
        value: props.orders.realname,
        check: props.orders.is_takeway == 1,
      },
      {
        display: true,
        component: Input,
        name: "phone",
        values: [],
        editShow: true,
        label: "下单手机号码",
        value: props.orders.phone,
        check: props.orders.is_takeway == 1,
      },
    ]);
    setDiscountData(props.orders?.pay_discount || "");
    // ListData.forEach((item, key) => {
    //     if (item.editShow) {
    //         FormData[item.name] = item.value;
    //     }
    // });
    //setFormData(FormData)

    // setOldDefaultFormData(props.orders);
  }, [props]);

  useEffect(() => {
    if (orderInfoData?.pay_type == 0 && orderInfoData?.order_status == 1) {
      setPay();
    }
  }, [orderInfoData]);

  const setPay = () => {
    // console.log(orderInfoData)
    // setFormData(props.orders);
    FormData["pay_type"] = props.defaultPayInfo;
    setFormData(FormData);
    // oldDefaultFormData = FormData;
    Modal.warn({
      title: "请设置支付方式",
      content: (
        <>
          <Form
            initialValues={orderInfoData}
            defaultValue={props.orders}
            onFinish={(values) => {
              // console.log(values, FormData);
            }}
          >
            <Radio.Group
              defaultValue={props.defaultPayInfo}
              options={props.payInfo}
              onChange={({ target: { value } }: RadioChangeEvent) => {
                console.log(value);
                FormData["pay_type"] = value;
                setFormData(FormData);
              }}
              optionType="button"
            ></Radio.Group>
          </Form>
        </>
      ),
      centered: true,
      keyboard: true,
      onOk: () => {
        if (FormData.pay_type != undefined && FormData.pay_type !== 0) {
          payOrder.run(props.orders.oid, FormData.pay_type);
        }
      },
    });
  };

  return (
    <div className="orderThead">
      {props.showDiscount && (
        <div className="orderDiscount" style={{ padding: "0 0 30px 0" }}>
          <div className="orderDiscountLabel" style={{ marginBottom: 10 }}>
            <span style={{ fontSize: 16 }}>订单折扣</span>
            <span style={{ fontSize: 16, color: "#e55c5c" }}>
              （算法：订单金额 - (订单金额 * 折扣百分比)，只对本次订单生效）
            </span>
          </div>
          <div className="orderDiscountContent">
            <Input.Group compact>
              <Input
                name="discount"
                value={discountData}
                onChange={(e) => setDiscountData(e.target.value)}
                placeholder="请输入折扣的百分比，如 10"
                suffix="%"
                style={{ width: 300 }}
              />
              <Button
                type="primary"
                onClick={() => {
                  discountOrder.run(props.orders.oid, discountData);
                }}
              >
                设置
              </Button>
            </Input.Group>
          </div>
        </div>
      )}
      <Form
        form={form}
        initialValues={props.orders}
        defaultValue={props.orders}
        onFinish={(values) => {
          console.log({
            ...FormData,
            ...values,
          });
          editOrder.run(props.orders.oid, {
            ...FormData,
            ...values,
          });
          setIsEdit(!isEdit);
        }}
      >
        <React.Fragment>
          {!isEdit ? (
            <Row className="orderItem">
              <Col span={6} className={"itemTitle itemOptions"}>
                更多操作
              </Col>
              <Col span={18} className="itemContent">
                <Button
                  type="primary"
                  size="large"
                  style={{ verticalAlign: "top", marginLeft: "15px" }}
                  danger={isEdit}
                  onClick={() => setIsEdit(!isEdit)}
                >
                  {isEdit ? "取消编辑" : "编辑订单"}
                </Button>
                <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>

                <Button
                  type="primary"
                  danger
                  size="large"
                  onClick={() => restOrder.run(props.orders.oid)}
                >
                  重置订单
                </Button>
                <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                {props.orders.order_status == 0 && (
                  <Button
                    type="primary"
                    size="large"
                    disabled={props.orders.is_cancel > 0}
                    onClick={() => setPay()}
                  >
                    已支付
                  </Button>
                )}

                {props.orders.order_status == 1 && (
                  <Button
                    type="primary"
                    size="large"
                    disabled={props.orders.is_cancel > 0}
                    onClick={() => confirmOrder.run(props.orders.oid)}
                  >
                    已完成
                  </Button>
                )}

                {props.orders.is_checked == 0 && (
                  <React.Fragment>
                    <span>
                      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    </span>
                    <Button
                      onClick={() => checkedOrder.run(props.orders.oid)}
                      type="primary"
                      style={{ background: "#26a755", borderColor: "#26a755" }}
                      size="large"
                    >
                      确认上菜
                    </Button>
                  </React.Fragment>
                )}
              </Col>
            </Row>
          ) : (
            <Row className="orderItem">
              <Col span={6} className="itemTitle itemOptions"></Col>
              <Col span={18} className="itemContent">
                <Button
                  type="primary"
                  size="large"
                  style={{ verticalAlign: "top", marginLeft: "15px" }}
                  danger={isEdit}
                  onClick={() => {
                    setIsEdit(!isEdit);
                  }}
                >
                  {isEdit ? "取消编辑" : "编辑订单"}
                </Button>
                <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                <Button type="primary" size="large" htmlType="submit">
                  提交
                </Button>
              </Col>
            </Row>
          )}
        </React.Fragment>

        {[...ListData].map((data: any, key: number) => {
          if (data.component === Radio.Group) {
            // FormData[data.name] = data.default;
            // setFormData(FormData);
            if (data.default != undefined) {
              form.setFieldsValue({
                [data.name]: data.default,
              });
            }
          }
          return (
            <React.Fragment key={key}>
              {data.check && data.display && isEdit === false && (
                <Row className="orderItem">
                  <Col span={6} className="itemTitle">
                    {data.label}
                  </Col>
                  <Col
                    span={18}
                    className="itemContent"
                    onClick={() => (
                      (ListData[key]["value"] = data.value + " ssss"),
                      setListData(ListData)
                    )}
                  >
                    {data.value}
                  </Col>
                </Row>
              )}

              {data.check && isEdit === true && data.editShow && (
                <Row className="orderItem">
                  <Col span={6} className="itemTitle">
                    {data.label}
                  </Col>
                  <Col span={18} className="itemContent">
                    <FormItem name={data.name} style={{ marginBottom: 0 }}>
                      {data.component === Switch && (
                        <Switch
                          onChange={(v: boolean) => {
                            form.setFieldsValue({
                              [data.name]: v ? 1 : 0,
                            });
                          }}
                          unCheckedChildren="否"
                          checkedChildren="是"
                        />
                      )}

                      {data.component === Input && (
                        <Input
                          defaultValue={orderInfoData[data.name]}
                          onChange={(v) => {
                            // FormData[data.name] = v.target.value;
                            // setFormData(FormData)
                            form.setFieldsValue({
                              [data.name]: v.target.value,
                            });
                          }}
                        />
                      )}
                      {data.component === Input.TextArea && (
                        <Input.TextArea
                          defaultValue={orderInfoData[data.name]}
                          onChange={(v) => {
                            // FormData[data.name] = v.target.value;
                            // setFormData(FormData)
                            form.setFieldsValue({
                              [data.name]: v.target.value,
                            });
                          }}
                        />
                      )}

                      {data.component === Select && (
                        <Select
                          showSearch
                          defaultValue={parseInt(orderInfoData[data.name])}
                          options={data.values}
                          onSelect={(v) => {
                            form.setFieldsValue({
                              [data.name]: v,
                            });
                          }}
                        ></Select>
                      )}

                      {data.component === Radio.Group && (
                        <Radio.Group
                          defaultValue={orderInfoData[data.name]}
                          options={data.values}
                          optionType="button"
                          onChange={(v) =>
                            form.setFieldsValue({
                              [data.name]: v.target.value,
                            })
                          }
                        ></Radio.Group>
                      )}
                    </FormItem>
                  </Col>
                </Row>
              )}
            </React.Fragment>
          );
        })}

        <React.Fragment>
          <Row className="orderItem">
            <Col span={6} className={"itemTitle itemOptions"}>
              更多操作
            </Col>
            <Col span={18} className="itemContent">
              <Button
                disabled={
                  props.orders.order_status == 1 || props.orders.is_cancel == 1
                }
                onClick={() => cancelOrder.run(props.orders.oid)}
                type="primary"
                danger
                size="large"
              >
                取消订单
              </Button>
            </Col>
          </Row>
        </React.Fragment>
      </Form>
    </div>
  );
}
