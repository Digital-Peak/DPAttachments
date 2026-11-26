### Introduction
When the plugin "Web Services - DPAttachments" is enabled, attachments can be managed through the Joomla API under `/api/index.php/v1/dpattachments/attachments`. Authenticate with a Joomla API token, the user needs the "Access Web Services" permission of DPAttachments. All other permission checks are the same as on the website, so an attachment can be uploaded when the user can edit the item it belongs to.

### Routes
| Method | Route | Description |
|---|---|---|
| GET | `/v1/dpattachments/attachments` | Lists the attachments, the supported query parameters are described below. |
| GET | `/v1/dpattachments/attachments/{id}` | Returns a single attachment including a `download_url`. |
| POST | `/v1/dpattachments/attachments` | Uploads a new attachment. Required are `context`, `item_id`, `path` (the file name) and `content` (the base64 encoded file). |
| PATCH | `/v1/dpattachments/attachments/{id}` | Updates the attachment. When `content` is set, the file is replaced. The `context`, `item_id` and `path` can't be changed. |
| DELETE | `/v1/dpattachments/attachments/{id}` | Deletes a trashed attachment and its file. |

The custom fields of attachments are available under `/v1/fields/dpattachments/attachments` and `/v1/fields/groups/dpattachments/attachments`.

### List parameters
The list of attachments can be filtered, ordered and paginated with the following query parameters. Filters can be combined, then only the attachments are returned which match all of them. Attachments the user is not allowed to see are never returned.

| Parameter | Description |
|---|---|
| `filter[context]` | Only attachments of the given context, for example `com_content.article` or `com_dpcalendar.event`. |
| `filter[item_id]` | Only attachments of the item with the given ID. Mostly used together with `filter[context]`, as the same ID can exist in different contexts. |
| `filter[state]` | Only attachments with the given state: `1` published, `0` unpublished, `2` archived and `-2` trashed. When not set, all attachments except the trashed ones are returned. |
| `filter[access]` | Only attachments with the given access level ID, for example `1` for public. |
| `filter[author]` | Only attachments created by the user with the given ID. |
| `filter[search]` | Searches in the title and description. With the prefix `id:` the attachment with the given ID is returned, for example `id:12`. With the prefix `author:` it searches in the name and username of the creator, for example `author:john`. |
| `list[ordering]` | The column to order by: `a.id`, `a.title`, `a.state`, `a.access`, `a.created`, `a.created_by`, `a.hits`, `a.publish_up` or `a.publish_down`. The default is `a.created`. |
| `list[direction]` | The direction of the ordering, `asc` or `desc`. The default is `asc`. |
| `page[limit]` | The amount of attachments per page, default is 20. |
| `page[offset]` | The amount of attachments to skip. |

For example to get the published attachments of the article with the ID 12, newest first:

```
curl -G https://example.com/api/index.php/v1/dpattachments/attachments \
  -H "Authorization: Bearer YOUR_TOKEN" \
  --data-urlencode "filter[context]=com_content.article" \
  --data-urlencode "filter[item_id]=12" \
  --data-urlencode "filter[state]=1" \
  --data-urlencode "list[ordering]=a.created" \
  --data-urlencode "list[direction]=desc"
```

### Upload example
To attach a PDF to the article with the ID 12:

```
curl -X POST https://example.com/api/index.php/v1/dpattachments/attachments \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "context": "com_content.article",
    "item_id": "12",
    "title": "Manual",
    "path": "manual.pdf",
    "content": "'"$(base64 -w0 manual.pdf)"'"
  }'
```
