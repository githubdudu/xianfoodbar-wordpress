import "../../bootstrap";
import "./layouts.scss";

import * as Icons from "@ant-design/icons";
import {
  ClearOutlined,
  DashboardOutlined,
  DeleteOutlined,
  LogoutOutlined,
  MenuFoldOutlined,
  MenuUnfoldOutlined,
  PlusOutlined,
  RedoOutlined,
  RollbackOutlined,
  UnorderedListOutlined,
} from "@ant-design/icons";
import { useRequest } from "ahooks";
import {
  Avatar,
  Breadcrumb,
  Button,
  Col,
  Drawer,
  Dropdown,
  Form,
  Layout,
  Menu,
  PageHeader,
  Row,
  message,
  notification,
  Modal,
} from "antd";
import { Howl } from "howler";
import React, { ReactNode, useEffect, useRef, useState } from "react";
import { useCallback } from "react";
import { Link, useHistory, useRouteMatch } from "react-router-dom";

import OrderAdd from "../orderAdd";
import useConfig from "./useConfig";
import { ItemType } from "antd/lib/menu/hooks/useItems";

import canAutoPlay, { CheckResponse } from "can-autoplay";
import { useSignals } from "@preact/signals-react/runtime";
import { signal } from "@preact/signals-react";

interface MenuDataItem {}

window.onunload = (e) => {
  e.preventDefault();
  (window as any).nowEvent?.close();
  (window as any).messageEvent?.close();
};

export const LayoutContext: React.Context<any> = React.createContext<any>({
  IsMobile: false,
  setIsMobile: (v: boolean) => {},
  eventSourcesList: [],
  setEventSourcesList: (v: any) => {},
  cloaseAllEventSource: (v: any) => {},
  menuChange: () => {},
});

const menuList2 = sessionStorage.getItem("menus");
const menus = JSON.parse(menuList2 || "[]") || [];
const dash = sessionStorage.getItem("dash");
const dashJson = JSON.parse(dash || "{}");
const configStorage = sessionStorage.getItem("config");
const config = JSON.parse(configStorage || "{}");

const menuList = signal(menus);
const AdminUserConfig = signal(config);

export default function AdminLayouts(props: { children?: any }) {
  const history = useHistory();
  const match = useRouteMatch();

  const root_url = (window as any).root_admin || "/admin";
  const root_path = (window as any).default_path || "/";
  const [eventSourcesList, setEventSourcesList] = useState([]);
  const [Colla, setColla] = useState(false);
  const [IsMobile, setIsMobile] = useState(false);
  const [ShowDrawer, setShowDrawer] = useState<boolean>(false);
  const [DashConfig, setDashConfig] = useState({
    title: "仪表盘",
    ...dashJson,
  } as {
    title: string;
  });
  const [form] = Form.useForm();
  const video = useRef<HTMLAudioElement>(null);
  const [MenuLoadding, setMenuLoadding] = useState(false);
  const { Config, SetConfig, setIsMobile: setIsMobile2 } = useConfig();
  const [SysConfig, setSysConfig] = useState(Config || {});
  const [SetMobileStatus, setSetMobileStatus] = useState(false);
  const [ShowFade, setShowFade] = useState(false);

  const [dropDownMenu, setDropDownMenu] = useState<ItemType[]>([]);
  const [nowRoute, setNowRoute] = useState("");
  const [lastRoute, setLastRoute] = useState("");

  const menuChange = (call: () => void) => call();

  const loopMenuItem = (menus: []): any[] =>
    menus.map((item: any) => {
      if (item.icon && item.icon.name) {
        let options: {
          twoToneColor?: string;
          style?: object;
        } = {};

        if (item.icon.color) options["twoToneColor"] = item.icon.color;
        if (item.icon.style) options["style"] = item.icon.style;
        // @ts-ignore
        item.icon = Icons[item.icon.name];
      }

      if (item.children) item.children = loopMenuItem(item.children);

      return item;
    });

  const getList = useRequest(
    () => ({
      url: "/api/admin/getMenus",
    }),
    {
      manual: true,
      onSuccess: (data) => {
        if (data.dash) {
          sessionStorage.setItem("dash", JSON.stringify(data.dash));
          setDashConfig({
            title: data.dash.title,
          });
        }

        if (data.menu_list) {
          setMenuLoadding(true);
          // @ts-ignore
          menuList.value = data.menu_list;
          sessionStorage.setItem("menus", JSON.stringify(data.menu_list));
          setMenuLoadding(false);
        }
      },
      onError: (error: any, params: any) => {
        notification.error({
          message: "系统错误",
          description: "网络连接错误或未知错误",
        });
      },
    },
  );

  const getAdminConfig = useRequest("/api/admin/get/admin/config", {
    manual: true,
    onSuccess(data: any) {
      if (data.config) {
        sessionStorage.setItem("config", JSON.stringify(data.config));
      }
      AdminUserConfig.value = data.config || {};
      // setAdminUserConfig(data.config || {});
    },
    onError(e) {
      notification.error({
        message: "获取管理信息失败",
      });
    },
  });

  const clearCache = useRequest("/api/admin/clear/cache", {
    manual: true,
    onSuccess(data: any) {
      // setAdminUserConfig(data.config || {});
      notification.success({
        message: data.message,
      });
    },
    onError(e) {
      notification.error({
        message: "缓存清除失败",
      });
    },
  });

  useEffect(() => {
    if (Config) {
      setSysConfig(Config);
    }
  }, [Config]);

  useEffect(() => {
    let key = 0;
    if (AdminUserConfig.value?.menus) {
      let menus = AdminUserConfig.value?.menus?.map((item: any) => ({
        label: (
          // @ts-ignore
          <Link to={item.url || root_url}>{item.menu_name}</Link>
        ),
        key: "drop_" + key++,
      }));
      const baseMenus = [
        {
          key: "drop_" + key++,
          label: (
            <a
              style={{ color: "#d64a4a", fontWeight: "bold" }}
              onClick={() => clearCache.run()}
            >
              清除缓存
            </a>
          ),
          icon: <DeleteOutlined />,
        },
        {
          key: "drop_" + key++,
          label: (
            <a
              style={{ color: "#d64a4a", fontWeight: "bold" }}
              href={root_url + "/logout"}
            >
              登出
            </a>
          ),
          icon: <LogoutOutlined color="#d64a4a" />,
        },
        {
          key: "clear_browser",
          label: (
            <span
              style={{ color: "red" }}
              onClick={() => {
                window.sessionStorage.clear();
                window.location.reload();
              }}
            >
              清除浏览器缓存
            </span>
          ),
          icon: <ClearOutlined />,
        },
      ];

      setDropDownMenu([...menus, ...baseMenus]);
    }
  }, [AdminUserConfig.value?.menus]);

  useEffect(() => {
    if (menuList2) {
      // menuList.value = menus
    } else {
      getList.run();
    }
    if (configStorage) {
      // AdminUserConfig.value = config
    } else {
      getAdminConfig.run();
    }

    let showed = false;
    setTimeout(
      () =>
        canAutoPlay.audio().then(({ result }) => {
          if (result === false) {
            if (showed) return;
            Modal.warn({
              title: "无法自动播放声音",
              content: "点击确认开启",
              cancelText: null,
            });
            showed = true;
          } else {
            showed = false;
          }
        }),
      1000,
    );
    const audios: any = {};
    const service = () => {
      // fetch('/api/admin/message-notifications', {
      //   headers: {
      //     'Content-Type': 'application/json'
      //   },
      //   credentials: 'include',
      //   method: 'GET',
      //   keepalive: true,
      // }).then(res => res.json()).then(res => {
      //
      // }).catch(e => {
      //
      // })
      //  }
      const eventListen = new EventSource("/api/admin/message-notifications", {
        withCredentials: true,
      });
      (window as any).messageEvent = eventListen;

      eventListen.addEventListener("message", (e: MessageEvent) => {
        // data = JSON.parse(data);
        // console.log(e);
        const data = JSON.parse(e.data);
        if (data.voice) {
          let player: any;
          try {
            new Howl({
              src: [data.voice],
              html5: true,
              autoplay: true,
            });
          } catch (e) {}
          // const audio = new Audio(data.voice)
          // audio.play().catch(e => {
          //   player.play()
          // });
        }
        notification.info({
          message: data.title,
          description: data.content,
        });
      });

      eventListen.onerror = () => {
        eventListen.close();
        service();
      };
    };

    service();
  }, []);

  useEffect(() => {
    if (history.location.pathname.indexOf("system/table") === -1) {
      (window as any).oldRequest = "";
    }
  }, [history.location]);

  const cloaseAllEventSource = useCallback(() => {
    eventSourcesList.forEach((item: EventSource) => {
      item.close();
    });
    setEventSourcesList([]);
  }, [eventSourcesList]);

  useEffect(() => {
    if (nowRoute !== lastRoute) {
      eventSourcesList.forEach((item: EventSource) => {
        item.close();
      });
      setEventSourcesList([]);
      setLastRoute(nowRoute);
    }
  }, [nowRoute]);

  const Data = {
    IsMobile,
    setIsMobile,
    eventSourcesList,
    setEventSourcesList,
    cloaseAllEventSource,
    menuChange,
  };

  const MenuLink = (props: {
    useLink: boolean;
    useBlank?: boolean;
    link: string;
    label?: any;
  }) => {
    return (
      <>
        {/* @ts-ignore */}
        {props.useLink ? (
          <Link
            onClick={() => {
              if (SetMobileStatus) {
                setColla(!Colla);
                setShowFade(false);
                cloaseAllEventSource();
              }
              setNowRoute(props.link);
            }}
            to={props.link}
          >
            {props.label}
          </Link>
        ) : (
          <a target={props.useBlank ? "_blank" : "_self"} href={props.link}>
            {props.label}
          </a>
        )}
      </>
    );
  };

  return (
    <LayoutContext.Provider value={Data}>
      <Layout hasSider className="page-layout">
        <audio ref={video}></audio>
        <Layout.Sider
          breakpoint="sm"
          onBreakpoint={(borken: boolean) => {
            setIsMobile(borken);
            setSetMobileStatus(borken);
            setIsMobile2(borken);
          }}
          collapsed={Colla}
          zeroWidthTriggerStyle={
            ShowFade ? { display: "block" } : { display: "none" }
          }
          style={
            SetMobileStatus
              ? { position: "fixed", zIndex: 100 }
              : { position: "relative" }
          }
          collapsible
          onCollapse={() => (setColla(!Colla), setShowFade(false))}
          className="page-side"
          collapsedWidth={SetMobileStatus ? 0 : 80}
        >
          {SetMobileStatus && ShowFade && (
            <div
              className="fide"
              onClick={() => (setColla(!Colla), setShowFade(false))}
              style={{
                position: "fixed",
                width: "100%",
                background: "rgba(0, 0, 0, 0.6)",
                right: 0,
                top: 0,
                height: "100%",
              }}
            ></div>
          )}
          <div
            style={{
              position: SetMobileStatus ? "relative" : "fixed",
              top: 0,
              left: 0,
            }}
          >
            <div className="logo">
              {/* @ts-ignore */}
              <Link to={root_url}>
                <img
                  src={`${root_path}/public/build/logo.png`}
                  width={40}
                  alt=""
                />
                {Colla === false && <span>管理后台</span>}
              </Link>
            </div>
            <Menu
              theme="dark"
              defaultActiveFirst
              defaultSelectedKeys={["admin"]}
              mode="inline"
            >
              <Menu.Item key={"admin"} icon={<DashboardOutlined />}>
                <MenuLink
                  useBlank={false}
                  useLink={true}
                  link={root_url}
                  label={DashConfig.title}
                />
              </Menu.Item>
              {menuList.value.map((data: any, key: number) => {
                return (
                  // @ts-ignore
                  <Menu.Item key={data.key} icon={<UnorderedListOutlined />}>
                    <MenuLink
                      useLink={data.useLink}
                      link={data.path}
                      label={data.name}
                      useBlank={data.useBlank}
                    />
                  </Menu.Item>
                );
              })}
            </Menu>
          </div>
        </Layout.Sider>
        <Layout style={{ paddingTop: "80px" }}>
          <Layout.Header
            className="page-header"
            style={
              SetMobileStatus
                ? { width: "100%", padding: "0 20px" }
                : {
                    padding: "0 20px 0 0",
                    width: "calc(100% - 200px)",
                    zIndex: 555,
                  }
            }
          >
            <div className="header-menu-top">
              <div className="header-menu-top-left">
                {SetMobileStatus && (
                  <Button
                    style={{ position: "relative", zIndex: 99 }}
                    onClick={() => (setColla(!Colla), setShowFade(!ShowFade))}
                    icon={
                      !Colla ? <MenuUnfoldOutlined /> : <MenuFoldOutlined />
                    }
                  ></Button>
                )}

                <div
                  onClick={() => setShowDrawer(true)}
                  className="openMenu"
                  style={{
                    background: "#1890ff",
                    float: "left",
                    height: "100%",
                    padding: "0 30px",
                    color: "#fff",
                    fontSize: "14px",
                    fontWeight: "bold",
                    cursor: "pointer",
                  }}
                >
                  {" "}
                  <PlusOutlined /> 点菜
                </div>
              </div>
              <div
                className="header-menu-top-right"
                style={{ textAlign: "right", padding: 0 }}
              >
                <Dropdown
                  menu={{
                    items: dropDownMenu,
                  }}
                >
                  <Avatar src={""} />
                </Dropdown>
              </div>
            </div>
          </Layout.Header>
          <Layout.Content className="page-container">
            {SysConfig?.ShowHeader === true && (
              <PageHeader
                className="page-container-header"
                breadcrumbRender={() => (
                  <Breadcrumb>
                    <Breadcrumb.Item href={root_url}>
                      {" "}
                      <Icons.HomeOutlined /> 首页
                    </Breadcrumb.Item>
                    <Breadcrumb.Item>
                      <Icons.TableOutlined /> {SysConfig?.title}{" "}
                    </Breadcrumb.Item>
                  </Breadcrumb>
                )}
                title={SysConfig?.title}
                subTitle={SysConfig?.sub_title}
                extra={
                  <>
                    <Button
                      onClick={() => history.go(0)}
                      icon={<RedoOutlined />}
                    >
                      刷新页面
                    </Button>
                    <Button
                      onClick={() => history.goBack()}
                      icon={<RollbackOutlined />}
                    >
                      返回
                    </Button>
                  </>
                }
              >
                {SysConfig?.Header}
              </PageHeader>
            )}
            <div className="page-content">
              {React.cloneElement(props.children, {
                setData: () => console.log(),
              })}
            </div>
          </Layout.Content>
          <Layout.Footer>
            <Drawer
              width={SetMobileStatus ? "100%" : "90%"}
              height="auto"
              open={ShowDrawer}
              onClose={() => setShowDrawer(false)}
              footer={
                <div
                  style={{
                    textAlign: "center",
                  }}
                >
                  <Button
                    size="large"
                    type="primary"
                    danger
                    onClick={() => setShowDrawer(false)}
                    style={{ marginRight: 8 }}
                  >
                    关闭
                  </Button>
                  <Button
                    size="large"
                    onClick={() => {
                      form.submit();
                      setShowDrawer(false);
                    }}
                    type="primary"
                  >
                    提交
                  </Button>
                </div>
              }
            >
              <OrderAdd form={form} />
            </Drawer>
          </Layout.Footer>
        </Layout>
      </Layout>
    </LayoutContext.Provider>
  );
}
