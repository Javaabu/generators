---
title: Supported Field Types
sidebar_position: 2
---

This package works by mapping database schema column types to a custom `Field` class. The supported field types and their corresponding database column types are given below.

| Field Type      | Column Types                                                                  |
|-----------------|-------------------------------------------------------------------------------|
| BooleanField    | `tinyint(1)`                                                                  |
| DateField       | `date`                                                                        |
| DateTimeField   | `datetime`, `timestamp`                                                       |
| DecimalField    | `double`, `decimal`, `dec`, `float`                                           |
| EnumField       | `enum`, `set`, any column that has a comment in the format `enum:<EnumClass>` |
| ForeignKeyField | `Foreign`                                                                     |
| IntegerField    | `tinyint`, `smallint`, `mediumint`, `int`, `bigint`                           |
| JsonField       | `json`                                                                        |
| StringField     | `varchar`, `char`                                                             |
| TextField       | `text`, `tinytext`, `mediumtext`, `longtext`                                  |
| TimeField       | `time`                                                                        |
| YearField       | `year`                                                                        |

Currently, morphs and pivots are not supported. Any unsupported column type will simply be skipped by the generators. 
