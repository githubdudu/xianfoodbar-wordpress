interface Menus {
    // 菜单名
    menu_name: string,
    // 菜单标志
    menu_flag: string,
    // 菜单链接
    menu_link: string,
    // 菜单类型
    menu_type: number,
}

interface TableSettingButton {
    ajax?: string,
    icon?: any,
    link?: string,
    ajaxIncludeData?: any,
    ajaxType?: string,
    text?: string,
    color?: string,
    className?: string,
    changeText?: string,
    dialog?: boolean,
    forms?: any[],
    ajaxId?: string,
    changeRule?: string,
    isRouter?: boolean,
}

export {
    Menus,
    TableSettingButton,
}