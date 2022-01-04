Stores a record per term with type in a language.

Part of the \ref md_docs_storage_terms storage system.

**Fields:**

-   wbtl_id - an auto increment field
-   wbtl_type_id - reference to the [wbt_type] table
-   wbtl_text_in_lang_id - reference to the [wbt_text_in_lang] table

```
+----------------------+------------------+------+-----+---------+----------------+
| Field                | Type             | Null | Key | Default | Extra          |
+----------------------+------------------+------+-----+---------+----------------+
| wbtl_id              | int(10) unsigned | NO   | PRI | NULL    | auto_increment |
| wbtl_type_id         | int(10) unsigned | NO   | MUL | NULL    |                |
| wbtl_text_in_lang_id | int(10) unsigned | NO   | MUL | NULL    |                |
+----------------------+------------------+------+-----+---------+----------------+
```

**Extra Indexes:**
 - UNIQUE wbtl_text_in_lang_id, wbtl_type_id
 - wbtl_type_id

[wbt_type]: @ref md_docs_sql_wbt_type
[wbt_text_in_lang]: @ref md_docs_sql_wbt_text_in_lang
