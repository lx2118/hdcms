#学生薪资模块

##获取学生列表

```
<tag action="salary.lists" order="id desc" row="3">
    {{$field['name']}}
</tag>
```
所有字段都可以进行排序: desc降序 asc升序, row属性获取条数