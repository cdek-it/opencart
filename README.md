# Модуль CDEK Delivery Shipping для OpenCart

## Описание

Данное расширение OpenCart добавляет метод доставки "CDEK Delivery Shipping Method". Оно позволяет рассчитывать стоимость доставки на основе различных параметров и интегрироваться с API CDEK для беспрепятственной обработки и отслеживания заказов.

## Установка

### Для разработки

1. Клонируйте этот репозиторий в отдельную папку на вашей машине.
2. Запустите скрипт `install.sh`, указав маршрут к папке OpenCart:

   ```bash
   ./install.sh {route_to_opencart_folder}
    ```

    В результате необходимые файлы будут скопированы в соответствующие директории вашей установки OpenCart.

### Для тестирования

Архивный файл `opencart-cdek-delivery.ocmod.zip` находится в каталоге `archive/`. Вы можете установить этот ZIP-файл через интерфейс администратора OpenCart для тестирования.

Для создания свежего архива можно выполнить команду:

```bash
./archive.sh
```

### Удаление

Для удаления файлов плагина из установки OpenCart можно выполнить команду:

```bash
./delete.sh {route_to_opencart_folder}
```

