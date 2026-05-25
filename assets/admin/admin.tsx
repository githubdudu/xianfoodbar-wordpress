import "../styles/admin.scss";

import { Column, Line, Pie } from "@ant-design/charts";
import ProCard, { StatisticCard, StatisticProps } from "@ant-design/pro-card";
import { useRequest } from "ahooks";
import { Card, Col, List, Row } from "antd";
import React, { useEffect, useState } from "react";
import ReactDOM from "react-dom";
import { Link, NavLink } from "react-router-dom";

import AdminLayouts from "./components/admin-layouts";
import useConfig from "./components/useConfig";

export default function Admin() {
  const { SetConfig, Config, IsMobile } = useConfig();

  const [AdminConfig, setAdminConfig] = useState({
    showInfoBar: true,
    useSudoku: false,
    inforBarTitle: "",
    infoData: [],
    sudokuList: [],
    columnConfig: {
      title: "",
      data: [],
      xField: "",
      yField: "",
      label: {
        position: "middle",
        style: {},
      },
      meta: {},
    },
    columns: [
      {
        list: [
          {
            title: "",
            style: {},
            type: null,
            config: {
              meta: {},
              label: {},
              xField: "",
              yField: "",
              angleField: "",
              colorField: "",
              data: [],
            },
          },
        ],
      },
    ],
  });

  const getAdminBarInfo = useRequest("/api/admin/config", {
    manual: true,
    onSuccess: (data: any) => setAdminConfig(data.config),
  });

  useEffect(() => {
    getAdminBarInfo.run();
    SetConfig({
      ...Config,
      title: "管理后台",
      ShowHeader: false,
    });
  }, []);
  return (
    <div className="admin">
      {AdminConfig.showInfoBar && (
        <StatisticCard.Group
          direction={IsMobile ? "column" : "row"}
          title={AdminConfig.inforBarTitle}
        >
          {AdminConfig.infoData.map((data: StatisticProps, key: number) => (
            <StatisticCard
              key={key}
              statistic={{
                title: data.title,
                tip: data.tip || "",
                value: data.value,
                suffix: data.suffix || "",
                precision: data.precision || 0,
                prefix: data.prefix || "",
                layout: IsMobile ? "horizontal" : "vertical",
              }}
            ></StatisticCard>
          ))}
        </StatisticCard.Group>
      )}

      <ProCard
        split={IsMobile ? "horizontal" : "vertical"}
        style={{ marginTop: 15 }}
      >
        <ProCard
          split={IsMobile ? "horizontal" : "vertical"}
          style={{ padding: 15 }}
        >
          {AdminConfig.useSudoku && (
            <div>
              <div className="tips" style={{ marginBottom: 24 }}>
                <span className="tips_red"></span> 使用中{" "}
                <span style={{ marginLeft: 24 }}></span> 空闲
              </div>
              <Row gutter={[2, 6]}>
                {AdminConfig.sudokuList &&
                  AdminConfig.sudokuList.map((data: any, key: number) => (
                    <Col
                      key={key}
                      className="sudoku_col"
                      span={IsMobile ? 8 : 0}
                    >
                      <div
                        className={
                          "sudoku_data_col " +
                          (data.use_status > 0
                            ? "sudoku_data_col_selected"
                            : "")
                        }
                      >
                        {data.desk_name}
                      </div>
                    </Col>
                  ))}
              </Row>
            </div>
          )}
        </ProCard>
      </ProCard>

      {AdminConfig.columns.map((data, key) => (
        <ProCard
          key={key}
          split={IsMobile ? "horizontal" : "vertical"}
          style={{ marginTop: 15 }}
        >
          {data.list.map((item, index) => (
            <ProCard
              key={index}
              title={item.title || ""}
              style={item?.style || {}}
            >
              {item.type == "column" && <Column {...item.config} />}
              {item.type == "pie" && <Pie {...item.config} />}
              {item.type == "line" && <Line {...item.config} />}
            </ProCard>
          ))}
        </ProCard>
      ))}
    </div>
  );
}

// ReactDOM.render(<Admin />, document.querySelector("#admin"));
