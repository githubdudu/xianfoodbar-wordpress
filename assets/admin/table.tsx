import '../bootstrap'
import '../styles/table.scss'

import * as Icons from '@ant-design/icons'

import { Button, Card, Col, Descriptions, Input, Row, Space, Spin, Table, notification } from 'antd'
import ProTable, { EditableProTable } from '@ant-design/pro-table'
import React, { useEffect, useRef, useState } from 'react'
import { useHistory, useParams } from 'react-router'

import Qrcode from 'qrcode.react'
import Requests from './components/Requests'
import TableLayoutButton from './components/tableButtonLayout'
import type { TableSettingButton } from '../types/AdminTypes'
import useConfig from './components/useConfig'
import { useRequest } from 'ahooks'

const HtmlContent = {
  Qrcode: Qrcode,
  Button: Button,
  Input: Input,
};

interface ActionType {
  reload: (resetPageIndex?: boolean) => void;
  reloadAndRest: () => void;
  reset: () => void;
  clearSelected?: () => void;
  startEditable: (rowKey: any) => boolean;
  cancelEditable: (rowKey: any) => boolean;
}

function UserFunc(funcString: any) {
  return (new Function(funcString))();
}

function AdminTable(props: any) {
  const colType: any[] = []
  const tableRef = useRef<ActionType>();
  const { name, anymethod } = useParams<{
    name: string,
    anymethod: string
  }>()
  const router = useHistory()
  const [Columns, setColumns] = useState(colType)
  const { Config, SetConfig, IsMobile } = useConfig();
  const [TableConfig, setTableConfig] = useState({
    description: "管理",
    sub_title: "管理",
    title: "管理",
    column: [],
    api_list: {
      data: "",
    },
    showSelect: false,
    buttons: [],
    tool_buttons: [],
    alert_buttons: [],
    tableData: [],
    tableDataTitle: "",

  } as any);
  const [SelectedList, setSelectedList] = useState([]);
  const [Loading, setLoading] = useState(true);
  const [Params, setParams] = useState({})
  const [DataValue, setDataValue] = useState([] as any)

  const emptyRequest = useRequest((api_link: string = '', id: number | string = 0, method: string = "GET", body: any = {}) => ({
    url: `${api_link}/${id}`,
    method: method,
    body: JSON.stringify(body)
  }), {
    manual: true,
    onSuccess: (data: any) => {
      notification.success({
        message: data.title,
        description: data.message,
      });
      (window as any).oldRequest = '';
      if (tableRef.current) {
        // @ts-ignore
        tableRef.current.reloadAndRest()

      }
    },
    onError: (e) => notification.error({
      message: '请求错误: ' + e.name,
      description: e.message,
    })
  });

  const configInit = (config: any) => {
    const tempColumns: any[] = [];

    const replaceString = (string: any, recore: any, text: any) => {
      if (typeof string !== 'string' || string.indexOf('$') === -1) {
        return string;
      }

      let tempKey = "";
      let word: any = "";

      string.replace(/\$(.*?)\$/, (v) => {
        tempKey = v;
        word = v.replace(/\$/g, '')
        return v;
      });

      if (word.indexOf('Function(') === 0) {
        let $return = (new Function('recore', 'text', 'return ' + (word.replace(/^Function\((.*?)\)$/, '$1')) + ';'))(recore, text);
        if (typeof $return === 'string') {
          return string.replace(tempKey, $return);
        }
        return $return;
      }

      if (word == 'text') {
        if (typeof text === 'string') {
          return string.replace(tempKey, text);
        }
        return text;
      }

      if (typeof recore[word] === 'string') {
        return string.replace(tempKey, recore[word]);
      }
      return recore[word];
    }

    const loopProps = (props: any, recore: any, text: any) => {
      let value: any = {};
      let regex = new RegExp('\$(.*?)\$', 'g');
      for (let i in props) {
        if (props[i] === undefined) {
          continue;
        }

        if (typeof i === "string" && i.indexOf('on') === 0) {
          let ajaxConfig: any = props[i];
          if (typeof ajaxConfig === "object") {
            let url: any = ajaxConfig.url
            if (regex.test(props[i])) {
              url = replaceString(url, recore, text);
            }

            let request = new Requests();
            request.setUrl(url)
              .setIdData(recore[ajaxConfig.id || 'id'])
              .setMethod(ajaxConfig.method || 'POST')
              .setIncludeData(ajaxConfig.includeData || recore)
              .setRef(tableRef)
            // @ts-ignore
            value[i] = () => {
              // @ts-ignore
              request.run()
            }
          }
          continue;
        }

        if (typeof props[i] === "object") {
          value[i] = loopProps(props[i], recore, text);
          continue;
        } else {
          if (typeof props[i] === "string" && regex.test(props[i])) {
            value[i] = replaceString(props[i], recore, text);
            continue;
          }
        }
        value[i] = props[i];
      }
      return value;
    };

    config.column.forEach((columns: any) => {
      let newColumn = {
        ...columns
      };
      if (columns.render) {
        newColumn.render = (text: any, recore: any) => {
          let string: any = columns.render;
          let rule: string = "NULL";
          let tag: string = string[0] || "";
          let props: any = string[1];

          string = string[2] || "";

          props = loopProps(props, recore, text);

          if (tag === '' && string === '') {
            return <></>;
          }

          if (string !== '') {
            let newString = replaceString(string, recore, text)
            //@ts-ignore
            return React.createElement(HtmlContent[tag] || tag, props, newString);
          }

          // @ts-ignore
          return React.createElement(HtmlContent[tag] || tag, props);
        }
      }
      tempColumns.push(newColumn);
    });
    tempColumns.push({
      title: '设置',
      valueType: 'option',
      align: 'center',
      render: (text: any, recore: any) => (
        <React.Fragment>
          {config.buttons.length > 0 && config.buttons.map((data: TableSettingButton, key: number) => {
            return <React.Fragment key={key}>
              <TableLayoutButton
                isRouter={data.isRouter}
                ajax={data.ajax}
                link={data.link}
                hidden={data.changeRule && (new Function("recore", "return (" + data.changeRule + ");"))(recore)}
                color={data.color}
                // @ts-ignore
                icon={data.icon ? Icons[data.icon] : 'span'}
                className={"setting-btn " + (data.className || "")}
                ajaxId={recore[data.ajaxId || 'id']}
                ajaxType={data.ajaxType}
                onSuccess={() => {
                  (window as any).oldRequest = '';
                  if (tableRef.current) {

                    // @ts-ignore
                    tableRef.current.reloadAndRest();
                  }
                }}
                ajaxIncludeData={data.ajaxIncludeData}
              >{data.text}</TableLayoutButton>
            </React.Fragment>
          })}
        </React.Fragment>
      ),
    });
    setColumns(tempColumns);

    SetConfig({
      ...Config,
      title: config.title,
      sub_title: config.sub_title,
      Header: config.description,
      ShowHeader: true,
    });

    setTableConfig(() => ({
      ...TableConfig,
      description: config.description,
      sub_title: config.sub_title,
      title: config.title,
      column: config.column,
      api_list: config.api_list,
      buttons: config.buttons || [],
      addButton: config.addButton,
      tool_buttons: config.tool_buttons || [],
      alert_buttons: config.alert_buttons || [],
      tableData: config.tableData || [],
      showSelect: config.showSelect,
      tableDataTitle: config.tableDataTitle || "",
    }));
  }

  const getDataConfig = useRequest((name: string = '', anymethod: string = '') => `/api/admin/system/table/config/${name}/${anymethod}`, {
    manual: true,
    onSuccess: (data: any) => {
      const config = data.config;
      sessionStorage.setItem(name + '_page', JSON.stringify(config || '{}'));
      configInit(config);
    }
  });

  const Index = () => {
    setLoading(true);
    const config = sessionStorage.getItem(name + '_page');
    if (config) {
      configInit(JSON.parse(config))
    } else {
      getDataConfig.run(name, anymethod)
    }

    if (tableRef.current) {
      // (window as any).oldRequest = '';
      tableRef.current.reloadAndRest();
    }
  }

  useEffect(() => {
    Index();
  }, [name, anymethod])
  // const addButton: TableSettingButton = TableConfig.addButton;

  useEffect(() => {
    // console.log(TableConfig)
    // @ts-ignore
    if (TableConfig.api_list.data !== "") {
      setLoading(false);
    }
  }, [TableConfig])

  useEffect(() => {
    Index();
  }, [])

  const convertUrl = (url: string) => {
    return url.indexOf('?') === -1 ? url + '?' : url + '&';
  }

  return (
    <Spin spinning={Loading}>
      <div className='table-content' style={{ minHeight: 300, padding: IsMobile ? '0' : '20px' }}>
        <div className="table">
          {Loading === false &&
            <ProTable
              // @ts-ignore
              actionRef={tableRef}
              // @ts-ignore
              request={async (params: any, sort: any, filter: any) => {
                // console.log();
                setParams(new URLSearchParams(params));
                const url = TableConfig.api_list.data + "?" + (new URLSearchParams(params)).toString();
                if (url == (window as any).oldRequest) {
                  return {};
                }
                // @ts-ignore
                const data = await fetch(url).then((res) => res.json());
                (window as any).oldRequest = url;
                return {
                  data: data.data,
                  // success 请返回 true，
                  // 不然 table 会停止解析数据，即使有数据
                  success: data.status === 200,
                  // 不传会使用 data 的长度，如果是分页一定要传
                  total: data.total
                }
              }}
              tableAlertRender={({ selectedRowKeys, selectedRows, onCleanSelected }) => {
                // @ts-ignore
                setSelectedList(selectedRows);
                return <Space size={24}>
                  <span>
                    已选 {selectedRowKeys.length} 项
                    <a style={{ marginLeft: 8 }} onClick={onCleanSelected}>
                      取消选择
                    </a>
                  </span>
                </Space>
              }}
              tableAlertOptionRender={() => <Space size={16}>
                {TableConfig.alert_buttons.map((data: TableSettingButton, key: number) => (
                  <React.Fragment key={key}>
                    {data.ajax && <a className={"alert-btn " + (data.className || "")}
                      onClick={() => emptyRequest.run(data.ajax, '', data.ajaxType || "GET", data.ajaxIncludeData || {})}
                      style={data.color ? { color: data.color, } : {}}>
                      {// @ts-ignore
                        React.createElement(data.icon ? Icons[data.icon] : 'span')} {data.text}
                    </a>}
                  </React.Fragment>
                ))}
              </Space>}
              rowSelection={TableConfig.showSelect === true ? {
                // 自定义选择项参考: https://ant.design/components/table-cn/#components-table-demo-row-selection-custom
                // 注释该行则默认不显示下拉选项
                selections: [Table.SELECTION_ALL, Table.SELECTION_INVERT],
              } : false}
              toolbar={{
                title: Config.title || '管理'
              }}
              tableExtraRender={(_: any, data: any) => (
                <React.Fragment>
                  {TableConfig.tableData && TableConfig.tableData.length > 0 &&
                    <Card title={TableConfig.tableDataTitle}>
                      <Descriptions size="small" column={3}>
                        {TableConfig.tableData.map((data: any, i: number) => (
                          <Descriptions.Item label={data.title}>{data.content}</Descriptions.Item>
                        ))}
                      </Descriptions>
                    </Card>}
                </React.Fragment>

              )}
              editable={{
                type: 'single',

              }}
              toolBarRender={() => TableConfig.tool_buttons.map((data: TableSettingButton) => <Button
                className={"setting-btn " + (data.className || "")}
                type="primary"
                // @ts-ignore
                onClick={() => data.isRouter ? router.push(data.link) : window.open(convertUrl(data.link) + Params.toString(), '_blank').focus()}
                // @ts-ignore
                icon={React.createElement(data.icon ? Icons[data.icon] : 'span')}
                style={data.color ? { background: data.color, borderColor: data.color } : {}}>
                {data.text}
              </Button>)}
              columns={
                // @ts-ignore
                [].concat(Columns)}></ProTable>}

          {Loading === false && false && <EditableProTable
            // @ts-ignore
            actionRef={tableRef}
            // @ts-ignore
            type='multiple'
            editable={{
              type: 'multiple',
              editableKeys: [128, 127, 126, 125],
              onSave: async () => {
              },
              onValuesChange(recore: any, recoreList: any[]) {
                setDataValue([recore, ...DataValue]);
              }
            }}
            rowKey='mid'
            recordCreatorProps={{
              newRecordType: 'dataSource',
              // 不写 key ，会使用 index 当行 id
              record: { key: (Math.random() * 1000000).toFixed(0), },
              // 设置按钮文案
              creatorButtonText: '新增一行',
              style: {
                display: 'none',
              },
            }}
            // @ts-ignore
            request={async (params: any, sort: any, filter: any) => {
              // console.log();
              setParams(new URLSearchParams(params));
              const url = TableConfig.api_list.data + "?" + (new URLSearchParams(params)).toString();
              // @ts-ignore
              const data = await fetch(url).then((res) => res.json());

              return {
                data: data.data,
                // success 请返回 true，
                // 不然 table 会停止解析数据，即使有数据
                success: data.status === 200,
                // 不传会使用 data 的长度，如果是分页一定要传
                total: data.total
              }
            }}
            toolBarRender={() => TableConfig.tool_buttons.map((data: TableSettingButton) => <Button
              className={"setting-btn " + (data.className || "")}
              type="primary"
              // @ts-ignore
              onClick={() => data.isRouter ? router.push(data.link) : window.open(convertUrl(data.link) + Params.toString(), '_blank').focus()}
              // @ts-ignore
              icon={React.createElement(data.icon ? Icons[data.icon] : 'span')}
              style={data.color ? { background: data.color, borderColor: data.color } : {}}>
              {data.text}
            </Button>)}
            columns={
              // @ts-ignore
              [].concat(Columns)} />}
        </div>
      </div>
    </Spin>
  )
}
export default AdminTable;
// ReactDOM.render(<AdminTable />, document.querySelector("#admin-table"));
