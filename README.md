# Manage your passwords 密码管家

这是一个简单的密码管理系统，使用mui搭建前端页面，php做后端，数据保存在bmob平台上。体验地址：http://mypass.ionepub.tk

示例如下：

![image](https://dn-shimo-image.qbox.me/XpRiTxf5J94ijpMy.png!thumbnail "登录页")

![image](https://dn-shimo-image.qbox.me/m9CC6lzfHQs7kIiN.png!thumbnail "新增记录")

需要修改的地方：

  1. cloud.php 中$base_pass_key的值，这是平台自定义的密钥，系统的密码通过此密钥加密，具体的加密方式为：
      `md5(md5(用户新增记录时提交的密钥) . 平台自定义密钥)`
  2. lib/BmobConfig.class.php
      需要先在bmob平台上注册，并新建应用，可获得应用的appid等信息，填入即可
  3. 在bmob创建的应用中新建表pwd
  ![image](https://dn-shimo-image.qbox.me/4Un1wU0WFpAoVJHA.png!thumbnail "数据库表结构")
  
  
