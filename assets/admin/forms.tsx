import "../styles/form.scss";

import { Button, Form, Modal, Upload, notification, Select } from "antd";
import FormRender, { useForm } from "form-render";
import React, { useEffect, useRef, useState } from "react";

import useConfig from "./components/useConfig";
import { useParams } from "react-router";
import { useRequest } from "ahooks";

const NewSelect = ({ value, ...rest }: any) => {
  return <Select value={value} {...rest} />;
};

function AdminForms() {
  // @ts-ignore
  const { name, anymethod, anyparam } = useParams();
  const [FormConfig, setFormConfig] = useState(
    {} as any & {
      column?: number;
      displayType?: string;
      edit?: any;
      formData?: any;
      id?: any;
      labelWidth?: number;
      now_type?: string;
      post?: any;
      readOnly?: boolean;
      return_url?: string;
      schema?: any;
      showDescIcon?: boolean;
    },
  );
  const form = useForm();
  const { Config, SetConfig, IsMobile } = useConfig();

  const emptyRequest = useRequest(
    (
      apiLink: string = "",
      method: string = "POST",
      body: any = {},
      basedId: string = "",
    ) => ({
      url: `${apiLink}` + (basedId ? "/" + basedId : ""),
      method: method,
      body: JSON.stringify(body),
      headers: {
        "Content-Type": "application/json",
      },
    }),
    {
      manual: true,
      onSuccess: (data: any) =>
        notification.success({
          message: data.title,
          description: data.message,
          onClose: () => {
            if (data.returnUrl) {
              location.href = data.returnUrl;
              return;
            }
            history.back();
          },
        }),
      onError: (e) =>
        notification.error({
          message: "请求错误: " + e.name,
          description: e.message,
        }),
    },
  );

  const getFormConfig = useRequest(
    (anyparam: any) =>
      `/api/admin/system/forms/config/${name}/${anymethod}/${anyparam || ""}`,
    {
      manual: true,
      onSuccess(data: any) {
        const config = data.config;

        SetConfig({
          ...Config,
          title: config.title,
          sub_title: config.sub_title,
          Header: config.description,
          ShowHeader: true,
        });

        if (config.formConfig.formData) {
          // setFormData(config.formConfig.formData);
          form.setValues(config.formConfig.formData);
        }

        setFormConfig(config.formConfig);
      },
    },
  );

  useEffect(() => {
    // if (formDataConfig) {
    //     setFormData(formDataConfig);
    // }
    getFormConfig.run(anyparam);
  }, [anyparam]);

  useEffect(() => {
    // if (formDataConfig) {
    //     setFormData(formDataConfig);
    // }
    getFormConfig.run(anyparam);
  }, []);

  return (
    <div
      className="formUi"
      style={{
        overflow: "hidden",
        marginBottom: 128,
        padding: IsMobile ? "60px 4%" : "60px 14%",
      }}
    >
      {FormConfig.schema && (
        <FormRender
          form={form}
          onFinish={(data: any, errors: any) => {
            if (errors.length > 0) {
              console.log(form.errorFields);
              Modal.error({
                title: "错误",
                content: "请确认输入无误再提交数据",
              });
              return false;
            }
            let apiLink = FormConfig.post.api;
            let method = FormConfig.post.method;

            if (FormConfig.now_type == "edit") {
              apiLink = FormConfig.edit.api;
              method = FormConfig.edit.method;
            }

            emptyRequest.run(
              apiLink || location.href,
              method,
              form.getValues(),
              anyparam || "",
            );
          }}
          schema={FormConfig.schema}
          widgets={{
            select2: NewSelect,
          }}
        />
      )}
      <div
        className="centerButton"
        style={{
          width: IsMobile ? "100%" : "calc(100% - 200px)",
          position: "fixed",
          bottom: 0,
          height: 80,
          lineHeight: 6,
          background: "#fff",
          borderTop: "1px solid #f0f0f0",
          textAlign: "center",
          right: 0,
        }}
      >
        <Button htmlType="submit" type="primary" onClick={() => form.submit()}>
          {" "}
          提交数据{" "}
        </Button>
        <Button
          htmlType="reset"
          style={{ marginLeft: 16 }}
          onClick={() => form.resetFields()}
        >
          重置
        </Button>
      </div>
    </div>
  );
}

export default AdminForms;
// ReactDOM.render(<AdminForms />, document.getElementById("admin-forms"));
