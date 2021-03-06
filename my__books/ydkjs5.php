<?php include $_SERVER['DOCUMENT_ROOT'].'/include/header.php'; ?>



<div class="nav_bar">
    <br>
    <p><i>Содержание:</i></p>
    <ul>
        <li><a class="list-sub__link" href="#chapter1">Глава 1: Асинхронность: сейчас и потом</a></li>
        <li><a class="list-sub__link" href="#chapter3">Глава 3: Обещания</a></li>
        <li><a class="list-sub__link" href="#chapter4">Глава 4: Генераторы</a></li>
        <li><a class="list-sub__link" href="#chapter5">Глава 5: Быстродействие программ</a></li>
    </ul>
</div>


<p>c 150 - Function.apply.bind</p>

<div class="linear" id="chapter1">

    <h2>Глава 1: Асинхронность: сейчас и потом</h2>

    <p>Было решено, что для распознавания обещаний (или
        чего-то, что ведет себя как обещание) будет правильно определить понятие <code>«thenable»</code> как любого объекта или функции, содержащих метод <code>then(..)</code> . Предполагается, что любое такое
        значение является <code>«thenable»</code> и соответствующим требованиям
        к обещаниям.</p>

    <br>

    <p><b>Цикл событий</b></p>

    <p>У всех управляющих сред (движок браузера) есть одна характерная особенность: у них
        существует механизм, который обеспечивает выполнение нескольких фрагментов вашей программы, обращаясь с вызовами
        к движку JS в разные моменты времени. Этот механизм называется <b><i>циклом событий</i></b>.</p>

    <p>Например, когда ваша программа JS выдает запрос <code>Ajax</code> для
        получения данных с сервера, вы определяете код реакции в функции (обычно называемой <b><i>функцией обратного вызова</i></b>, или просто
        <b><i>обратным вызовом</i></b>), а движок JS говорит управляющей среде:
        <i>«Так, я собираюсь ненадолго приостановить выполнение, но когда ты завершишь обработку этого сетевого запроса и получишь
            данные, пожалуйста, вызови вот эту функцию»</i>.</p>

    <p>Важно заметить, что функция <code>setTimeout(..)</code> не ставит обратный
        вызов в очередь цикла событий. Вместо этого она запускает таймер; по истечении таймера среда помещает ваш обратный вызов
        в цикл событий, чтобы некий тик в будущем подобрал его для
        выполнения.</p>

    <br>

    <p><b>Параллельные потоки</b></p>

    <p>Термины <b><i>«асинхронный»</i></b> и <b><i>«параллельный»</i></b> очень часто используются как синонимы, но в действительности они имеют разный
        смысл. Напомню, что суть асинхронности — управление промежутком между <b><i>«сейчас»</i></b> и <b><i>«потом»</i></b>. Параллелизм обозначает возможность одновременного выполнения операций.</p>

    <br>

    <p><b>Выполнение до завершения</b></p>

    <p>Программа JavaScript (практически) всегда разбивается на два
        и более блока: первый блок выполняется сейчас, а следующий блок
        будет выполняться потом в ответ на событие. Хотя программа
        выполняется по блокам, все они совместно используют одинаковый доступ к области видимости и состоянию программы, так что
        каждое изменение состояния осуществляется поверх предыдущего состояния.</p>

    <p>Каждый раз, когда в системе имеются события для обработки,
        цикл событий выполняется до тех пор, пока очередь не опустеет.
        Каждая итерация цикла событий называется тиком. Действияпользователя, операции ввода/вывода и таймеры помещают события в очередь событий.</p>

    <p>В любой момент времени может обрабатываться только одно событие из очереди. В то время, пока событие выполняется, оно
        может прямо или косвенно породить одно или несколько последующих событий.</p>

    <p>Под параллельным выполнением понимается чередование двух
        и более цепочек событий во времени, так что с высокоуровневой
        точки зрения они вроде бы выполняются одновременно (хотя
        в любой момент времени обрабатывается только одно событие).</p>

    <p>Часто бывает необходимо в той или иной форме координировать
        выполнение параллельных «процессов» (не путать с процессами
        операционной системы!), например, чтобы гарантировать упорядочение или предотвратить состояние гонки. Эти «процессы»
        также могут выполняться в кооперативном режиме, для чего они
        разбиваются на меньшие блоки и дают возможность выполняться
        другим чередующимся «процессам».</p>

</div>

<div class="linear" id="chapter3">

    <h2>Глава 3: Обещания</h2>

    <br>

    <p><b>Утиная типизация с методом then()(thenable)</b></p>

    <p>Общий термин для проверки типа, которая делает предположения
        относительно типа значения на основании его строения (то есть
        наличествующих свойств), называется <b><i>утиной типизацией</i></b> (см.
        книгу <i>«Типы и грамматические конструкции»</i> этой серии). Таким
        образом, проверка утиной типизации для <code>«thenable»</code> выглядит
        примерно так:</p>

        <pre class="brush: js;">
            if (
                p !== null &&
                    (
                        typeof p === "object" ||
                        typeof p === "function"
                    ) &&
                typeof p.then === "function"
            ) {
                //"thenable"!
            }
            else {
                // не "thenable"
            }
        </pre>

        <p>Если вы попытаетесь выполнить обещание с любым объектом/
            функцией, который по случайности содержит функцию <code>then(..)</code> ,
            но при этом не предполагалось, что он будет использоваться как
            обещание/<code>«thenable»</code>, вам не повезло. Объект будет автоматически
            распознан как <code>«thenable»</code> и будет обрабатываться по особым пра-
            вилам (см. далее в этой главе).</p>

        <p><b><i>Утиная типизация</i></b> с методом <code>then()</code>  может быть полезной, как вы
            вскоре убедитесь. Просто помните: <b><i>Утиная типизация</i></b> с мето-
            дом <code>then()</code> может быть опасна, если она ошибочно иденти-
            фицирует как обещание нечто, что обещанием не является.</p>

        <br>

        <p><b>Сцепление</b></p>

        <pre class="brush: js;">
            var p = Promise.resolve( 21 );
            p
            .then( function(v){
                console.log( v ); // 21
                // выполнение сцепленного обещания со значением `42`
                return v * 2;
            } )
            // сцепленное обещание
            .then( function(v){
                console.log( v ); // 42
            } );
        </pre>

        <p>Теперь первый вызов <code>then(..)</code>  становится первым шагом асин-
            хронной последовательности, а второй вызов <code>then(..)</code> — ее вторым
            шагом. Все это может продолжаться так долго, как потребуется.
            Просто продолжайте присоединять вызовы к предыдущим
            <code>then(..)</code>  с каждым автоматически созданным обещанием.</p>

        <p>Используя вызов <code>request(..)</code> , возвращающий обещание, мы соз-
            даем первое звено цепочки неявно, вызывая его с первым URL-
            адресом, и отходим от возвращенного обещания первым вызовом
            <code>then(..)</code> .</p>

        <pre class="brush: js;">
            // шаг 1:
            request( "http://some.url.1/" )
            // шаг 2:
            .then( function(response1){
                foo.bar(); // undefined, ошибка!
                // сюда управление не передается
                return request( "http://some.url.2/?v=" + response1 );
            } )
            // шаг 3:
            .then(
                function fulfilled(response2){
                    // сюда управление не передается
                },
                // обработчик отказа для перехвата ошибки
                function rejected(err){
                    console.log( err );
                    // ошибка `TypeError` из `foo.bar()`
                    return 42;
                }
            )
            // шаг 4:
            .then( function(msg){
                console.log( msg ); // 42
            } );
        </pre>

        <p>Когда на шаге 2 происходит ошибка, обработчик отказа на шаге 3
            перехватывает его. Возвращаемое значение от этого обработчика
            отказа (42 в данном фрагменте), если оно есть, выполняет обещание для следующего шага (4), так что цепочка снова находится
            в состоянии выполнения.</p>

        <br>

        <p><b>Паттерны обещаний</b></p>

        <p><code>У Promise.all([ .. ]) </code> все передаваемые обещания должны быть
            выполнены, чтобы выполнялось возвращаемое обещание. Если
            какое-либо обещание отклоняется, то и главное возвращаемое
            обещание тоже будет немедленно отклонено (с потерей результа-
            тов всех остальных обещаний). В случае выполнения вы полу-
            чаете массив значений выполнения всех переданных обещаний.
            В случае отказа вы получаете значение причины только для
            первого отклоненного обещания. Этот паттерн традиционно на-
            зывается <b><i>шлюзом</i></b>: чтобы шлюз открылся, должны прибыть все
            желающие пройти.</p>

        <p>У <code>Promise.race([ .. ])</code>  побеждает только первое разрешенное обе-
            щание (выполнение или отказ), и результат этого разрешения
            становится результатом разрешения возвращенного обещания.
            Этот паттерн традиционно называется защелкой: первый, кто от-
            крывает защелку, проходит далее.</p>

        <pre class="brush: js;">
            var p1 = Promise.resolve( 42 );
            var p2 = Promise.resolve( "Hello World" );
            var p3 = Promise.reject( "Oops" );
            Promise.race( [p1,p2,p3] )
            .then( function(msg){
                console.log( msg ); // 42
            } );
            Promise.all( [p1,p2,p3] )
            .catch( function(err){
                console.error( err ); // "Oops"
            } );
            Promise.all( [p1,p2] )
            .then( function(msgs){
                console.log( msgs ); // [42,"Hello World"]
            } );
        </pre>

        <p>Поскольку победит только одно обещание, значение выполнения
            представляет собой отдельное сообщение, а не массив, как было
            для <code>Promise.all([ .. ])</code> .</p>

        <p>none([ .. ]) — аналогичен all([ .. ]) , но выполнения и отказы
            меняются местами. Все обещания должны быть отклонены —
            отказы становятся значениями выполнения, и наоборот.</p>

        <p><b><i>any([ .. ])</i></b> — аналогичен <code>all([ .. ])</code> , но он игнорирует любые
        отказы, так что будет достаточно одного выполнения вместо
        всех.</p>

        <p><b><i>first([ .. ])</i></b> — аналогичен гонке с <code>any([ .. ])</code> ; это означает, что
        он игнорирует любые отказы и выполнения после первого вы-
        полнения обещания.</p>

        <p><b><i>last([ .. ])</i></b> — аналогичен <code>first([ .. ])</code> , но побеждает только
        последнее выполнение.</p>

        <br>

        <p><code>Promise.resolve(..)</code>  обычно используется для создания уже вы-
            полненных обещаний (по аналогии с <code>Promise.reject(..) </code>). Тем не
            менее <code>Promise.resolve(..)</code>  также распаковывает «thenable» зна-
            чения (как неоднократно упоминалось выше). В этом случае воз-
            вращаемое обещание принимает результат итогового разрешения
            переданного «thenable» значения, которым может быть как вы-
            полнение, так и отказ:</p>

        <pre class="brush: js;">
            var fulfilledTh = {
                then: function(cb) { cb( 42 ); }
            };
            var rejectedTh = {
                then: function(cb,errCb) {
                    errCb( "Oops" );
                }
            };
            var p1 = Promise.resolve( fulfilledTh );
            var p2 = Promise.resolve( rejectedTh );
            // `p1` будет выполненным обещанием
            // `p2` будет отклоненным обещанием
        </pre>

        <p>И помните: метод <code>Promise.resolve(..)</code>  ничего не сделает, если
            переданное значение уже является полноценным обещанием, он
            просто вернет это значение. А значит, вызов <code>Promise.resolve(..)</code>
            для значений, природа которых вам неизвестна, не приведет к по-
            тере быстродействия, если переданное значение окажется полно-
            ценным обещанием.</p>

        <p>Функция <code>Promise.wrap(..)</code> не создает обещания, она создает функ-
            цию, которая создает обещания. В каком-то смысле функция,
            производящая обещания, может рассматриваться как фабрика
            обещаний.</p>

        <br>

        <p><b>then(..) и catch(..)</b></p>

        <p><code>catch(..)</code>  получает в параметре только обратный вызов отказа
            и автоматически заменяет обратный вызов по умолчанию для
            выполнения, как указано выше. Иначе говоря, вызов эквивалентен
            <code>then(null,..)</code> :</p>

        <pre class="brush: js;">
            p.then( fulfilled );
            p.then( fulfilled, rejected );
            p.catch( rejected );        // или `p.then( null, rejected )`
        </pre>

</div>

<div class="linear" id="chapter4">

    <h2>Глава 4: Генераторы</h2>

    <pre class="brush: js;">
        var x = 1;
        function *foo() {
            x++;
            yield; // приостановка!
            console.log( "x:", x );
        }
        function bar() {
            x++;
        }

        // сконструировать итератор `it` для управления генератором
        var it = foo();

        // здесь запускается `foo()`!
        it.next();
        x; // 2
        bar();
        x; // 3
        it.next(); // x: 3
    </pre>

    <ul class="ul_num">
        <li>Операция <code>it = foo()</code>  еще не выполняет генератор <code>*foo()</code> — она
            всего лишь конструирует итератор, который будет управлять
            его выполнением. Вскоре итераторы будут рассмотрены более
            подробно.</li>
        <li>Первая команда <code>it.next()</code>  запускает генератор <code>*foo()</code>  и вы-
            полняет <code>x++</code> в первой строке <code>*foo()</code> .</li>
        <li><code>*foo()</code>  приостанавливает выполнение на команде <code>yield</code>, и пер-
            вый вызов <code>it.next()</code>  завершается. В этот момент <code>*foo()</code>  про-
            должает работать и сохраняет активность, но находится в при-
            остановленном состоянии.</li>
        <li>Проверяем значение <code>x</code>, оно сейчас равно <code>2</code>.</li>
        <li>Вызываем функцию <code>bar()</code> , которая снова увеличивает x коман-
            дой <code>x++</code>.</li>
        <li>Снова проверяем значение <code>x</code>, на этот раз оно равно <code>3</code>.</li>
        <li>Итоговый вызов <code>it.next()</code>  возобновляет выполнение генера-
            тора <code>*foo()</code> с точки приостановки и выполняет команду console.
            <code>log(..)</code> , в которой используется текущее значение <code>x</code>, равное <code>3</code>.</li>
    </ul>

    <br>

    <p><b>Ввод и вывод</b></p>


    <pre class="brush: js;">
    function *foo(x,y) {
        return x * y;
    }
    var it = foo( 6, 7 );
    var res = it.next();
    res.value; // 42
    </pre>

    <p>Здесь мы видим отличие в вызове генератора по сравнению
        с нормальной функцией. Конечно, запись <code>foo(6,7)</code>  выглядит зна-
        комо, но при этом генератор <code>*foo(..)</code>  еще не запустился, как это
        было бы с обычной функцией.</p>

    <p>Вместо этого мы создаем объект-итератор для управления гене-
        ратором <code>*foo(..) </code> и присваиваем его переменной <code>it</code>. После этого вызывается метод <code>it.next()</code> , который приказывает генератору
        <code>*foo(..)</code>  переместиться от текущей позиции либо до следующей
        позиции <code>yield</code>, либо до конца генератора.</p>

    <p>Результатом вызова <code>next(..)</code>  является объект со свойством <code>value</code>,
        которое содержит значение, возвращенное <code>*foo(..)</code>. Иначе говоря,
        <code>yield</code> приводит к передаче значения генератором в середине его
        выполнения — что-то вроде промежуточного return.</p>

    <br>

    <p><b>Передача сообщений при итерациях</b></p>

    <pre class="brush: js;">
        function *foo(x) {
            var y = x * (yield);
            return y;
        }
        var it = foo( 6 );
        // запустить `foo(..)`
        it.next();
        var res = it.next( 7 );
        res.value; // 42
    </pre>

    <p>Сначала значение <code>6</code> передается в параметре <code>x</code>. Затем вызывается
    метод <code>it.next()</code> , который запускает <code>*foo(..) </code>.</p>

    <p>Внутри <code>*foo(..)</code>  начинается обработка команды <code>var y = x ..</code> , но тут
    же встречается выражение <code>yield</code>. В этой точке выполнение <code>*foo(..)</code> приостанавливается (в середине команды присваивания!), а вы-
    зывающий код фактически должен предоставить результат для
    выражения <code>yield</code>. Вызов метода <code>it.next( 7 )</code>  передает значение <code>7</code>
    обратно как результат приостановленного выражения <code>yield</code>.</p>

    <p>Итак, на этой стадии команда присваивания фактически прини-
    мает вид <code>var y = 6 * 7</code>. Теперь <code>return</code> y возвращает значение <code>42</code> как
    результат вызова <code>it.next( 7 )</code> .</p>
    
    <br>

    <p><b>Итерируемые объекты</b></p>

    <p>В ES6 для получения <b><i>итератора итерируемый объект</i></b> должен
        содержать функцию, имя которой задается специальным симво-
        лическим значением ES6 <code>Symbol.iterator</code>. При вызове эта функция
        возвращает <i>итератор</i>. Обычно каждый вызов должен возвращать
        новый <i>итератор</i>, хотя это и не обязательно.</p>

    <pre class="brush: js;">
        var a = [1,3,5,7,9];
        var it = a[Symbol.iterator]();

        it.next().value; // 1
        it.next().value; // 3
        it.next().value; // 5
        ..
    </pre>

    <br>

    <p><b>Итераторы генераторов</b></p>

    <p>Например, цикл <code>while..true</code> сообщает, что генератор должен работать бесконечно
         — продолжать генерировать значения, пока мы продол-
        жаем их запрашивать.</p>
    <p>А теперь новый генератор <code>*something()</code>  можно использовать с 
        циклом <code>for..of;</code> как вы увидите, он работает практически идентично:</p>

    <pre class="brush: js;">
        function *something() {
            var nextVal;
            while (true) {
                if (nextVal === undefined) {
                    nextVal = 1;
                }
                else {
                    nextVal = (3 * nextVal) + 6;
                }
                yield nextVal;
            }
        }

        for (var v of something()) {
            console.log( v );
            // цикл не должен работать бесконечно!
            if (v > 500) {
                break;
            }
        }
        // 1 9 33 105 321 969
    </pre>

    <p>Обратите особое внимание на <code>for (var v of something())</code> ! Мы не
        просто ссылаемся на <code>something</code> как на значение, как в предыдущих
        примерах, а вызываем генераторcode <code> *something()</code>  для получения его
        итератора, чтобы использовать его в цикле <code>for..of</code>.</p>

    <br>

    <p><b>Синхронная обработка ошибок</b></p>

    <pre class="brush: js;">
        try {
            var text = yield foo( 11, 31 );
            console.log( text );
        }
        catch (err) {
            console.error( err );
        }
    </pre>

    <p>Самое замечательное здесь то, что приоста-
        новка <code>yield</code> также позволяет генератору перехватить ошибку.
        Ошибка запускается в генератор следующей частью приведенно-
        го выше кода:</p>

    <pre class="brush: js;">
        if (err) {
            // выдать ошибку в `*main()`
            it.throw( err );
        }
    </pre>

    <p>Принцип работы генераторов «<code>yield-приостановка</code>» означает, что
        мы не только получаем синхронно выглядящие возвращаемые
        значения от асинхронных вызовов функций, но и можем 
    синхронно перехватывать ошибки от этих асинхронных вызовов функций!</p>

    <pre class="brush: js;">
        function *main() {
            var x = yield "Hello World";
            // управление сюда не передается
            console.log( x );
        }

        var it = main();
        it.next();

        try {
            // обработает ли эту ошибку `*main()`? посмотрим!
            it.throw( "Oops" );
        }
        catch (err) {
            // нет, не обработает!
            console.error( err ); // Неудача
        }
    </pre>

    <br>

    <p><b>ES7: async и await?</b></p>

    <p>Существует специальный синтаксис для работы с промисами, который называется <code>async/await</code>.</p>

    <p><code>async</code> это функция всегда возвращает промис. Значения других типов оборачиваются в завершившийся успешно промис автоматически.</p>

    <p><code>await</code> заставит интерпретатор JavaScript ждать до тех пор, пока промис справа от <code>await</code> не выполнится. После чего оно вернёт его результат, и выполнение кода продолжится.</p>

    <pre class="brush: js;">
        function foo(x,y) {
            return request(
            "http://some.url.1/?x=" + x + "&y=" + y
            );
        }

        async function main() {
            try {
                var text = await foo( 11, 31 );
                console.log( text );
            }
            catch (err) {
                console.error( err );
            }
        }

        main();
    </pre>

    <p>Как видите, для запуска и управления <code>main()</code>  не нужен вызов
        <code>run(..)</code>  (а значит, не нужна и библиотечная поддержка!) —code   main()
        просто вызывается как обычная функция. Кроме того, <code>main()</code>
        более не объявляется как функция-генератор; это новая 
        разновидность функций — асинхронная функция (<code>async function</code>). И 
        наконец, вместо того чтобы передавать обещание через <code>yield</code>, мы
        просто ожидаем его завершения при помощи <code>await</code>.</p>

    <p>Функция <code>async function</code> уже знает, что нужно делать при ожидании
        обещания, — она приостанавливает функцию (как в случае с 
        генераторами) до разрешения обещания. В приведенном фрагменте
        это не показано, но вызов такой асинхронной функции, как <code>main()</code>,
        автоматически возвращает обещание, которое разрешается при
        полном завершении функции.</p>

    <br>

    <p><b>Делегирование асинхронности</b></p>

    <pre class="brush: js;">
        function *foo() {
            var r2 = yield request( "http://some.url.2" );
            var r3 = yield request( "http://some.url.3/?v=" + r2 );
            return r3;
        }

        function *bar() {
            var r1 = yield request( "http://some.url.1" );
            var r3 = yield *foo();
            console.log( r3 );
        }

        run( bar );
    </pre>

    <p>Вместо вызова <code>yield run(foo)</code>  внутри <code>*bar()</code>  мы просто вызываем
       <code> yield *foo()</code> .</p>

    <br>

    <p><b>Преобразователи</b></p>

    <p>В общей теории обработки данных существует старая, появив-
        шаяся еще до JS концепция преобразователей (<code>thunk</code>). Это узкоспециализированное 
        выражение преобразователя в JS является функция,
        которая без каких-либо параметров подключается для вызова
        другой функции.</p>

    <p>Иначе говоря, вызов функции (со всеми необходимыми параме-
        трами) «заворачивается» в определение функции, которое созда-
        ет промежуточное звено для выполнения внутренней функции.
        Внешняя функция-обертка и называется преобразователем. Позд-
        нее при выполнении преобразователя в конечном итоге будет
        вызвана исходная функция.</p>

    <pre class="brush: js;">
        function foo(x,y) {
            return x + y;
        }

        function fooThunk() {
            return foo( 3, 4 );
        }

        // позднее
        console.log( fooThunk() ); // 7
    </pre>

    <br>

    <p><b>Генераторы</b> — новая разновидность функций ES6, которые не 
        выполняются до завершения, как обычные функции. Вместо этого
        генератор может быть приостановлен на середине завершения
        (с полным сохранением состояния), а позднее продолжить работу
        с точки приостановки.</p>

    <p>В основе переходов от приостановки к продолжению работы лежит
        принцип кооперативной работы, а не вытеснения; это означает,
        что только сам генератор может приостановить себя, используя
        ключевое слово <code>yield</code>, и только итератор, управляющий 
        генератором, может (при помощи <code>next(..)</code> ) возобновить выполнение
        генератора.</p>

    <p>Дуализм <code>yield/next(..)</code> — не просто механизм управления; в 
        действительности это механизм двусторонней передачи сообщений.
        Выражение <code>yield</code> ..  фактически приостанавливается в ожидании
        значения, а следующий вызов <code>next(..)</code>  передает значение (или
        неявное <code>undefined</code>) приостановленному выражению <code>yield</code>.</p>

    <p>Главное преимущество генераторов, связанное с управлением
        асинхронной программной логикой, заключается в том, что код
        внутри генератора выражает последовательность шагов задачи
        естественным синхронным/последовательным образом. Фокус
        в том, что потенциальная асинхронность прячется за ключевым
        словом <code>yield</code>, то есть асинхронность перемещается в код, 
        управляющий итератором генератора.</p>

    <p>Другими словами, генераторы поддерживают паттерн последова-
        тельного синхронного блокирующего кода по отношению к асин-
        хронному коду. Это позволяет нашему мозгу более естественно
        анализировать код и устраняет один из двух ключевых недостат-
        ков асинхронности на базе обратных вызовов.</p>

</div>

<div class="linear" id="chapter5">

    <h2>Глава 5: Быстродействие программ</h2>

    <p>Экземпляр веб-работника создается в главной программе JS (или
        в другом веб-работнике) следующим образом:</p>

    <pre class="brush: js;">
        var w1 = new Worker( "http://some.url.1/mycoolworker.js" );
    </pre>

    <p>Поскольку общий работник может быть соединен с одним или
        несколькими экземплярами программы или страницы вашего
        сайта, он должен каким-то способом узнать, от какой программы
        пришло сообщение. Этот механизм однозначной идентификации
        называется портом (по аналогии с портами сетевых сокетов).
        Таким образом, вызывающая программа должна использовать для
        обмена данными объект port работника:</p>

    <pre class="brush: js;">
        w1.port.addEventListener( "message", handleMessages );
        // ..
        w1.port.postMessage( "something cool" );

        // Кроме того, подключения к портам должны инициализироваться:
        w1.port.start();
    </pre>

    <p>Внутри общего работника необходимо обрабатывать дополни-
        тельное событие: "<code>connect</code>" . Это событие предоставляет объект
        <code>port</code> для этого конкретного подключения. Самый удобный способ
        раздельного поддержания нескольких одновременных подключе-
        ний основан на использовании замыканий на основании <code>port</code>,
        с прослушиванием и передачей событий для этого подключения
        внутри обработчика для события "<code>connect</code>":</p>

    <pre class="brush: js;">
        // внутри общего работника
        addEventListener( "connect", function(evt){

        // порт для этого подключения
        var port = evt.ports[0];
        port.addEventListener( "message", function(evt){

        // ..
       
        port.postMessage( .. );
            // ..
        } );

        // инициализировать подключение через порт
        port.start();
        } );
    </pre>
</div>




<!--
<code></code>
<p></p>
<p><b></b></p>
<pre class="brush: js;">

</pre>

<div class="linear" id="chapter5">

    <h2>Глава 5: </h2>


</div>

<ul class="ul_num">
    <li></li>
</ul>

Разрешение экрана:
ширина: 1280 - 1264
высота: 800
-->

<?php include $_SERVER['DOCUMENT_ROOT'].'/include/footer.php'; ?>
