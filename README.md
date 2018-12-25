# PbootCMS

### PbootCMS是翱云科技开发的全新内核的PHP开源企业建站系统，系统以高效. 简洁. 强悍为开发目标，能够满足各类企业网站建设的需要。
* 系统采用高效. 简洁的模板标签，只要懂HTML就可快速开发企业网站。
* 系统采用PHP语言开发，使用自主研发的高速MVVM多层开发框架及多级缓存技术。
* 系统默认采用Sqlite轻型数据库，放入PHP空间即可直接使用，可选Mysql. Pgsql等数据库，满足各类存储需求。
* 系统采用响应式管理后台，满足各类设备随时管理的需要。

##  特色功能：
* 支持自定义内容模型；
* 支持多语言区域建站；
* 支持小程序、APP等对接；
* 支持自定义留言表单；
* 支持多条件筛选及搜索；
* 支持全站伪静态及前端动态缓存；
* 支持后台在线升级；

##  简洁强大的标签：
```
1、全局标签示意：
{pboot:sitetitle} 站点标题 
{pboot:sitelogo} 站点logo

2、列表页标签示意：
{pboot:list num=10 order=date}
	<p><a href="[list:link]">[list:title]</a></p>
{/pboot:list}

3、内容页标签示意：
{content:title} 标题
{content:subtitle}副标题
{content:author} 作者
{content:source} 来源

更多简单到想哭的标签请参考标签手册...

```

##  主要功能：
* 支持自定义模板
* 支持站点信息后台配置
* 支持无限极栏目
* 支持自定义内容模型
* 支持自定义内容字段
* 支持专题单页内容
* 支持列表内容管理
* 支持内容复制移动
* 支持自定义栏目地址
* 支持自定义内容地址
* 支持多语言区域建站
* 支持手机独立模板
* 支持手机版域名绑定
* 支持首页分页
* 支持页面SEO优化
* 支持在线留言
* 支持幻N组灯片轮播
* 支持友情链接
* 支持自定义表单
* 支持多条件筛选
* 支持多条件搜索
* 支持验证码开关
* 支持留言发送到多邮箱
* 支持API对接
* 支持小程序/APP开发
* 支持Ajax远程获取数据
* 支持自定义标签
* 支持全站伪静态
* 支持前端动态缓存
* 支持系统角色管理
* 支持完整角色权限管理
* 支持多用户在线管理
* 支持系统日志功能
* 支持数据库在线管理
* 支持后台在线升级

##  系统安装：

发布的源码默认采用Sqlite数据库，放入PHP（5.3+）空间即可直接使用。 

如果需要启用Mysql版本，请导入目录下数据库文件\static\backup\sql\xxx.sql，同时请注意使用最新日期名字的脚本文件，并修改config/database数据库连接文件信息。

注意：如果导入的数据库名字不一致，请先修改sql文件中数据库名为自己的。

系统后台默认访问路径：http://ip/admin.php   账号：admin   密码：123456，


##  升级说明：

* 使用后台在线升级（推荐）：

支持跨版本升级，会自动升级数据库及代码文件。

* 使用升级包升级：

不支持跨版本直接升级，需要一一升级，直接用升级包覆盖，
同时如果升级包中有升级数据库脚本时需要同时升级数据库。

* 使用全包升级：

支持跨版本升级，保留配置文件、模板文件、静态文件目录、数据库文件，其余全部用新版替换，
同时如果涉及到的中间版本有升级数据库，需要同时使用升级包中数据库脚本一一升级；


##  关于授权码：
授权码只为更好的统计安装情况，为离线验证，大家可在官网免费获取永久授权码。



##  联系我们：

技术交流群：137083872

官方网站：https://www.pbootcms.com/

GitHub：https://github.com/hnaoyun/PbootCMS

Gitee：https://gitee.com/hnaoyun/PbootCMS 
