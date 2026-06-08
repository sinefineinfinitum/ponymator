# Ponymator Syntax (PS) v1.0 — Language Examples

OOP and Procedural examples for each language according to the PS v1.0 specification.

---

## Ruby (OOP + Procedural)

### OOP — Class with inheritance and modules

```
@class final MyApp::Services::SearchService
>MyApp::Core::BaseService
<MyApp::Contracts::SearchInterface
%MyApp::Traits::LoggableMixin

$-readonly vector_store:MyApp::Storage::VectorStore
$-mixed_result:Integer|String|NilClass

!+DEFAULT_LIMIT:Integer=25

.+search final
    $query:MyApp::Query::SearchQuery
    :MyApp::Search::SearchResult|NilClass
    ^MyApp::Search::SearchResult

.+merge static
    &$source:Array
    $limit:Integer=10
    :Array

.+set_status
    $status:Integer|String
    :void
```

### Procedural — File

```
@file lib/functions.rb

.get_user
    $id:Integer
    :MyApp::Entity::User|NilClass

!MAX_RETRIES:Integer=3

$debug_mode:Boolean=false
```

---

## Java (OOP only)

### OOP — Class with interfaces and inheritance

```
@class final com.myapp.service.SearchService
>com.myapp.core.BaseService
<com.myapp.contract.SearchInterface
<com.myapp.contract.Loggable

$-readonly vectorStore:com.myapp.storage.VectorStore
$-mixedResult:int|String|Object

!+DEFAULT_LIMIT:int=25

.+search final
    $query:com.myapp.query.SearchQuery
    :com.myapp.search.SearchResult|Object
    ^com.myapp.search.SearchResult

.+merge static
    &$source:List
    $limit:int=10
    :List

.+setStatus
    $status:int|String
    :void
```

---

## C# (OOP only)

### OOP — Class with interfaces and inheritance

```
@class sealed Com.MyApp.Service.SearchService
>Com.MyApp.Core.BaseService
<Com.MyApp.Contract.ISearchInterface
<Com.MyApp.Contract.ILoggable

$-readonly VectorStore:Com.MyApp.Storage.VectorStore
$-MixedResult:int|string|object

!+DEFAULT_LIMIT:int=25

.+Search
    $query:Com.MyApp.Query.SearchQuery
    :Com.MyApp.Search.SearchResult|object
    ^Com.MyApp.Search.SearchResult

.+Merge static
    &$source:List
    $limit:int=10
    :List

.+SetStatus
    $status:int|string
    :void
```

---

## C++ (OOP only)

### OOP — Class with inheritance and interfaces

```
@class final CppApp::Services::SearchService
>CppApp::Core::BaseService
<CppApp::Contracts::SearchInterface
<CppApp::Contracts::Loggable

$-readonly vectorStore:CppApp::Storage::VectorStore
$-mixedResult:int|std::string|void*

!+DEFAULT_LIMIT:int=25

.+search final
    $query:CppApp::Query::SearchQuery
    :CppApp::Search::SearchResult*|nullptr
    ^CppApp::Search::SearchResult

.+merge static
    &$source:std::vector
    $limit:int=10
    :std::vector

.+setStatus
    $status:int|std::string
    :void
```

---

## Swift (OOP only)

### OOP — Class with protocol conformance

```
@class final MyApp.Services.SearchService
>MyApp.Core.BaseService
<MyApp.Contract.SearchInterface
<MyApp.Contract.Loggable

$-readonly vectorStore:MyApp.Storage.VectorStore
$-mixedResult:Int|String|Optional

!+DEFAULT_LIMIT:Int=25

.+search final
    $query:MyApp.Query.SearchQuery
    :MyApp.Search.SearchResult|Optional
    ^MyApp.Search.SearchResult

.+merge static
    &$source:Array
    $limit:Int=10
    :Array

.+setStatus
    $status:Int|String
    :Void
```

---

## Kotlin (OOP + Procedural)

### OOP — Class with inheritance and interfaces

```
@class final com.myapp.service.SearchService
>com.myapp.core.BaseService
<com.myapp.contract.SearchInterface
<com.myapp.contract.Loggable

$-readonly vectorStore:com.myapp.storage.VectorStore
$-mixedResult:Int|String|Any

!+DEFAULT_LIMIT:Int=25

.+search final
    $query:com.myapp.query.SearchQuery
    :com.myapp.search.SearchResult?
    ^com.myapp.search.SearchResult

.+merge static
    &$source:List
    $limit:Int=10
    :List

.+setStatus
    $status:Int|String
    :Unit
```

### Procedural — File

```
@file src/functions.kt

.getUser
    $id:Int
    :com.myapp.entity.User?

!MAX_RETRIES:Int=3

$debugMode:Boolean=false
```

---

## Dart (OOP + Procedural)

### OOP — Class with mixins and interfaces

```
@class final MyApp.Services.SearchService
>MyApp.Core.BaseService
<MyApp.Contract.SearchInterface
%MyApp.Mixin.Loggable

$-readonly vectorStore:MyApp.Storage.VectorStore
$-mixedResult:int|String|dynamic

!+DEFAULT_LIMIT:int=25

.+search final
    $query:MyApp.Query.SearchQuery
    :MyApp.Search.SearchResult?
    ^MyApp.Search.SearchResult

.+merge static
    &$source:List
    $limit:int=10
    :List

.+setStatus
    $status:int|String
    :void
```

### Procedural — File

```
@file lib/functions.dart

.getUser
    $id:int
    :MyApp.Entity.User?

!MAX_RETRIES:int=3

$debugMode:bool=false
```

---

## Python (OOP + Procedural)

### OOP — Class with inheritance and mixins

```
@class final myapp.services.SearchService
>myapp.core.BaseService
<myapp.contract.SearchInterface
%myapp.mixin.Loggable

$-readonly vector_store:myapp.storage.VectorStore
$-mixed_result:int|str|object

!+DEFAULT_LIMIT:int=25

.+search final
    $query:myapp.query.SearchQuery
    :myapp.search.SearchResult|None
    ^myapp.search.SearchResult

.+merge static
    &$source:list
    $limit:int=10
    :list

.+set_status
    $status:int|str
    :None
```

### Procedural — File

```
@file src/functions.py

.get_user
    $id:int
    :myapp.entity.User|None

!MAX_RETRIES:int=3

$debug_mode:bool=false
```

---

## JavaScript (OOP + Procedural)

### OOP — Class with inheritance and mixins

```
@class final MyApp.Services.SearchService
>MyApp.Core.BaseService
<MyApp.Contract.SearchInterface
%MyApp.Mixin.Loggable

$-readonly vectorStore:MyApp.Storage.VectorStore
$-mixedResult:number|string|object

!+DEFAULT_LIMIT:number=25

.+search final
    $query:MyApp.Query.SearchQuery
    :MyApp.Search.SearchResult|null
    ^MyApp.Search.SearchResult

.+merge static
    &$source:Array
    $limit:number=10
    :Array

.+setStatus
    $status:number|string
    :void
```

### Procedural — File

```
@file src/functions.js

.getUser
    $id:number
    :MyApp.Entity.User|null

!MAX_RETRIES:number=3

$debugMode:boolean=false
```

---

## TypeScript (OOP + Procedural)

### OOP — Class with inheritance and interfaces

```
@class final MyApp.Services.SearchService
>MyApp.Core.BaseService
<MyApp.Contract.SearchInterface
<MyApp.Contract.Loggable

$-readonly vectorStore:MyApp.Storage.VectorStore
$-mixedResult:number|string|object|undefined

!+DEFAULT_LIMIT:number=25

.+search final
    $query:MyApp.Query.SearchQuery
    :MyApp.Search.SearchResult|undefined
    ^MyApp.Search.SearchResult

.+merge static
    &$source:Array<unknown>
    $limit:number=10
    :Array<unknown>

.+setStatus
    $status:number|string
    :void
```

### Procedural — File

```
@file src/functions.ts

.getUser
    $id:number
    :MyApp.Entity.User|undefined

!MAX_RETRIES:number=3

$debugMode:boolean=false
```

---

## PHP (OOP + Procedural)

### OOP — Class

```
@class final App\Service\SearchService
>App\Core\BaseService
<App\Contracts\SearchInterface
%App\LoggableTrait

$-readonly vectorStore:App\Storage\VectorStore
$-mixedResult:int|string|null

!+DEFAULT_LIMIT:int=25

.+search final
    $query:App\Query\SearchQuery
    :App\Search\SearchResult|null
    ^App\Search\SearchResult

.+merge static
    &$source:array
    $limit:int=10
    :array

.+setStatus
    $status:int|string
    :void
```

### Procedural — File

```
@file src/functions.php

.getUser
    $id:int
    :App\Entity\User|null

!MAX_RETRIES:int=3

$debugMode:bool=false
```

---

## Go (Procedural + Struct-based)

### Struct-like Entity (mapped as `@class`)

```
@class GoApp.Services.SearchService

$-VectorStore:GoApp.Storage.VectorStore
$-MixedResult:int|string

!+DEFAULT_LIMIT:int=25

.Search
    $query:GoApp.Query.SearchQuery
    :GoApp.Search.SearchResult|error
    ^GoApp.Search.SearchResult

.Merge
    &$source:[]interface{}
    $limit:int=10
    :[]interface{}

.SetStatus
    $status:int|string
    :error
```

### Procedural — File

```
@file src/functions.go

.GetUser
    $id:int
    :GoApp.Entity.User|error

!MAX_RETRIES:int=3

$debugMode:bool=false
```

---

## Summary

The Ponymator Syntax v1.0 provides a **language-agnostic** graph representation. Each language binding adapts:

- **Naming conventions** (PascalCase, snake_case, camelCase, FQCN variations)
- **Type system** (nullable vs `|null`, `?` vs `|nil`, `error` returns in Go)
- **Visibility modifiers** (`+`, `-`, `#` mapped to language-specific keywords)
- **OOP constructs** (classes, interfaces, traits, mixins, protocols)
- **Procedural files** (`@file` with functions, constants, globals)

All examples maintain deterministic, graph-ready output suitable for documentation generation and dependency analysis across platforms.
