# 50ZF 武林支付
武林支付系统(ThinkPHP3.2.2内核,仿MCRMB)

使用GPL3.0开源协议

##说在前面
>这是我最近抽空写的一个作品,是当做练手的.(所以某些地方写的不好请见谅)
>这段时间武林支付平均每天上万的流水,经过十多万的流水也赚了上千了.
>这边真心感谢各位的支持.但是由于业内的恶性竞争,这几天[2016-08-29]一直再被大流量DDOS,
>我们的服务器实在撑不下去,不得不进行开源.

>也十分感谢这段时间支持武林支付的用户,
>这个程序,让我学到了很多,也让我明白了很多.

>本程序有完整的分成体系,支持为单用户设置特殊分成.
>也有提现体系和程序日志.
>支持直接上传到SAE使用,但要开启Memcached

>作者DDMCloud-Event 邮箱admin@DDMCloud.com

###程序自带API模块,开发文档请见Wiki
###数据库词典请前往Wiki查看

##安装方法
1. 安装网页服务器,配置PHP运行环境(支不支持PHP7我没有测试)
2. 安装MYSQL服务
3. 上传网页程序
4. 导入/SQL/mcpay.sql到你的数据库
5. 进入/Application/Home/Conf/config.php配置你的数据库信息
6. 安装Ucenter(如果安装了Discuz,则自带uc服务器,在论坛目录下的/uc_server/目录)
7. 在Ucenter创建应用程序,复制配置文件到/api/UcConfig.inc.php
8. 测试

#####友情提示,本程序默认使用的支付模块是龙梦云支付.
#####如有需要,可自行另外开发.
#####正式运营前,请在index.php中关闭debug模式,并且清空Runtime缓存
![](https://raw.githubusercontent.com/DDMCloud/50ZF/master/DDMCloud.jpg)