import '../bootstrap'
import '../styles/tabs.scss'

import { Badge, Card, List, Tabs } from 'antd';
import React, { useCallback, useContext, useEffect, useState } from 'react'
import { useLocation, useParams } from 'react-router';

import { LayoutContext } from './components/admin-layouts';
import { Links } from './components/links';
import useConfig from './components/useConfig';
import { useRequest } from 'ahooks';

interface TabsConfigInterface {
  tabs?: {
    title: string,
    name: string,
    badge?: string,
    checked?: boolean,
  }[],
  apiList?: {},
}

export default function AdminTabsComponent() {
  const { name, anymethod } = useParams<{
    name: string,
    anymethod: string
  }>()
  const { setEventSourcesList, eventSourcesList, cloaseAllEventSource } = useContext(LayoutContext);
  const query = new URLSearchParams(useLocation().search);
  const { Config, SetConfig } = useConfig();
  const [TabsConfig, setTabsConfig] = useState<TabsConfigInterface>({
    tabs: [],
    apiList: [],
  })
  const [ListData, setListData] = useState({});
  const [NowTabsKey, setNowTabsKey] = useState("")
  const [globalConfig, setGlobalConfig] = useState<any>({})
  const [BadgeValues, setBadgeValues] = useState({});

  const updateDataList = useCallback((data: any,) => {

    for (let name in data) {
      // @ts-ignore
      const nowBadge = data[name]?.badge || null;
      if (nowBadge !== null) {
        // @ts-ignore
        BadgeValues[name] = data[name]?.badge || null;

      }
      // @ts-ignore
      const dataList = data[name]?.list || [];
      if (dataList.length > 0) {
        // @ts-ignore
        ListData[name] = dataList;
      }
    }
    // @ts-ignore
    setBadgeValues({
      ...BadgeValues
    })
    setListData({
      ...ListData,
    })

  }, [BadgeValues, ListData]);

  const emptyLongRequest = (url: string) => {
    const reqLong = (url: string) => {
      const event = new EventSource(url);
      (window as any).nowEvent = event;
      // sessionStorage.setItem('event', event);
      //console.log(event)

      event.onmessage = (data: any) => {
        if (data.data) {
          data = JSON.parse(data.data);
          updateDataList(data)
        }
      }

      event.onerror = (e) => {
        // eventListen.close();
        console.log(e)
        event.close();
        reqLong(url);
      }

      return event;
    }

    const event = reqLong(url);

    // eventSourcesList.push(event);
    // setEventSourcesList(eventSourcesList);
    return event;
  }

  const emptyRequest = useRequest((url: string) => url, {
    manual: true,
    onSuccess: (data: any) => updateDataList(data)
  })

  async function sleep(ms: number) {
    return new Promise(resolve => setTimeout(resolve, ms));
  }

  const configInit = (config: any) => {
    const tabsConfig: TabsConfigInterface = config;

    SetConfig({
      ...Config,
      title: config.title,
      sub_title: config.sub_title,
      Header: config.description,
      ShowHeader: true,
    });

    setTabsConfig({
      ...config
    })

    if (tabsConfig.apiList) {
      cloaseAllEventSource();
      (window as any).nowEvent?.close();
      for (let i in tabsConfig.apiList) {
        // @ts-ignore
        let temp = tabsConfig.apiList[i];
        if (temp.isLong) {
          let e = emptyLongRequest(temp.url);
          // @ts-ignore
          globalConfig[i] = {
            stop: () => e.close(),
            start: (tabs: any) => {
              e.close();
              emptyLongRequest(temp.url)
            }
          }
        } else {
          emptyRequest.run(temp.url);
          // @ts-ignore
          globalConfig[i] = {
            stop: () => emptyRequest.cancel(),
            start: (tabs: any) => emptyRequest.run(temp.url)
          }
        }
        break;
      }
      setGlobalConfig({ ...globalConfig });
    }

    if (tabsConfig.tabs) {
      setNowTabsKey(tabsConfig.tabs[0].name);
      for (let i in tabsConfig.tabs) {
        let temp = tabsConfig.tabs[i];
        if (temp.checked && NowTabsKey === '') {
          setNowTabsKey(temp.name);
          break;
        }
        // if (temp.badge) {
        //     // @ts-ignore
        //     BadgeValues[temp.value] = temp.badge;
        //     setBadgeValues({
        //         ...BadgeValues,
        //     })
        // }
      }
    }

  }

  const getConfig = useRequest(`/api/admin/system/tabs/config/${name}/${anymethod}`, {
    manual: true,
    onSuccess: async (data: any) => {
      const config = data.config;
      sessionStorage.setItem(name + '_page', JSON.stringify(data.config));
      configInit(config);
    },
  });

  useEffect(() => {
    const config = sessionStorage.getItem(name + '_page');
    if (config) {
      configInit(JSON.parse(config));
    } else {
      getConfig.run();
    }
    if (query.get('activeKey')) {
      setNowTabsKey(query.get('activeKey') || NowTabsKey);
    }
  }, []);

  useEffect(() => {
    if (query.get('activeKey') && NowTabsKey === '') {
      setNowTabsKey(query.get('activeKey') || NowTabsKey);
    }
  }, [query]);


  // useEffect(() => {
  //     console.log(NowTabsKey)
  // }, [NowTabsKey])

  return (
    <div className="tabsSystem">
      <Tabs centered className="tabs" activeKey={NowTabsKey} onTabClick={(key: string) => {
        setNowTabsKey(key);
        // if (typeof globalConfig === 'object' && globalConfig[key]) {
        //   globalConfig[key]?.stop();
        //   globalConfig[key]?.start();
        // }
      }} type="card">
        {TabsConfig.tabs && TabsConfig.tabs.map((data: any, key: number) => (
          <Tabs.TabPane tab={
            <span className="tabClass">
              {data.title}<Badge size="default" className="badge" overflowCount={999} count={
                // @ts-ignore
                parseInt(BadgeValues[data.name] || 0)}></Badge>
            </span>
          } key={data.name}>
            <List
              grid={{ column: 10, gutter: 10, xs: 1, sm: 1, md: 3, lg: 4, xl: 5, xxl: 6 }}
              // @ts-ignore
              dataSource={[].concat(ListData[data.name] || [])} renderItem={(data: any, key: number) => (
                <List.Item style={{ height: '100%' }} key={key || 0}>
                  <Links link={data.link} isRouter={data.isRouter || false} isBlank={data.isBlank || false}>
                    {data.badge && <Badge.Ribbon text={data.badge}>
                      <Card className='tabsCard' style={data.style && data.style.root ? data.style.root : {}} >
                        <Card.Meta
                          title={
                            <div>
                              <span style={data.style && data.style.title ? data.style.title : {}} >{data.title}</span>
                              <span className='extra' style={data.style && data.style.extra ? data.style.extra : {}}>{data.extra}</span>
                            </div>}
                          description={<div style={data.style && data.style.content ? data.style.content : {}}>
                            {data.content instanceof Array && data.content.map((item: any, key: number) => (
                              <div>
                                <span style={{ fontWeight: 'bold' }}>{item.title} : </span>
                                <span style={{ display: 'inline-block', float: 'right' }}>
                                  {item.value && item.valueEnums ? item.valueEnums[item.value] : item.value}
                                  {item.count && item.count}
                                  {item.stuffer && item.stuffer}
                                </span>
                              </div>
                            ))}
                            {(data.content instanceof Array) === false && <div dangerouslySetInnerHTML={{ __html: data.content }}></div>}
                          </div>}
                        ></Card.Meta>
                      </Card>
                    </Badge.Ribbon>}
                    {!data.badge && <Card className='tabsCard' style={data.style && data.style.root ? data.style.root : {}} >
                      <Card.Meta
                        title={<div>
                          <span style={data.style && data.style.title ? data.style.title : {}} >{data.title}</span>
                          <span className='extra' style={data.style && data.style.extra ? data.style.extra : {}}>{data.extra}</span>
                        </div>}
                        description={<div style={data.style && data.style.content ? data.style.content : {}}>
                          {data.content instanceof Array && data.content.map((item: any, key: number) => (
                            <div key={key}>
                              <span style={{ fontWeight: 'bold' }}>{item.title} {item.title ? ':' : <br />} </span>
                              <span style={{ display: 'inline-block', float: 'right' }}>
                                {item.value && item.valueEnums ? item.valueEnums[item.value] : item.value}
                                {item.count && item.count}
                                {item.stuffer && item.stuffer}
                              </span>
                            </div>
                          ))}
                          {(data.content instanceof Array) === false && <div dangerouslySetInnerHTML={{ __html: data.content }}></div>}
                        </div>}
                      ></Card.Meta>
                    </Card>}
                  </Links>
                </List.Item>
              )}>
            </List>
          </Tabs.TabPane>))}
      </Tabs>

    </div>
  );
}
