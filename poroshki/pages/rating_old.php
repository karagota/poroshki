<div class="col-12 col-sm-12 col-lg-12 rating">
<style>table.rating-form tr td {
        padding: 10px 0;
    }

    .rating input, .rating select {
        height: 20px;
    }

    .rating_add_param {
        text-align: right;
    }
</style>


<h2>Рейтинг: настройка параметров</h2>

<form>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Пишущие авторы</h3>
    </div>
    <div class="panel-body">
        <table border="0" class="rating-form">
        <tr>
            <td valign="top" width="400"><label>написали хотя бы одну статью за:</label></td>
            <td><input type="text" size="4" name="PA_time" value="90"/> дней</td>
        </tr>
        </table>
    </div>
</div>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Рейтинг автора</h3>
    </div>
    <div class="panel-body">
        <table border="0" class="rating-form">
            <tr>
                <td valign="top" width="400"><label>Рейтинг непишушего автора равен:</label></td>
                <td><select name="RA_noarticle">
                        <option value="meanPA">Среднему рейтингу пишущих авторов</option>
                        <option value="const">Нулю</option>
                        <option value="const">Среднему рейтингу по всем авторам</option>
                    </select></td>
            </tr>


            <tr>
                <td valign="top" width="400"><label>Рейтинг пишущего автора:</label></td>
                <td><input type="text" name="RA_article_formula" style="width:280px;"
                           value="((a+0.5*(c-a) / (sqrt (b)) / b)+1)/2"/></td>
            </tr>

            <tr>
                <td colspan="2">
                    <hr/>
                </td>
            </tr>
            <tr class="rating_param">
                <td valign="top" width="400"><label>где a = </label></td>
                <td><select name="func_a">
                        <option value="Sum_R" selected>Сумма рейтинга</option>
                        <option value="count">Количество</option>
                        <option value="mean_R">Средний рейтинг</option>
                    </select>

                    <select name="a">
                        <option value="articles" selected>Статей</option>
                        <option value="authors">Авторов</option>
                        <option value="comments">Комментариев</option>
                        <option value="votes">Оценок</option>
                        <option value="cats">Категории</option>
                    </select> ,
                </td>
            </tr>
            <tr>
                <td valign="top" width="400" colspan="2"><label> отфильтрованных по:</label></td>

            </tr>
            <tr>
                <td valign="top" width="400"><input type="checkbox" name="a_filter_author" checked/><label>автору c
                        условием</label></td>
                <td><input type="a_filter_author_cond" value="данный автор"/></td>
            </tr>
            <tr>
                <td valign="top" width="400"><input type="checkbox" name="a_filter_date" checked/><label>дате публикации
                        c условием не старше</label></td>
                <td><input type="a_filter_date_cond" value="90"/>дней</td>
            </tr>
            <tr>
                <td valign="top" width="400"><input type="checkbox" name="a_filter_rating" checked/><label>рейтингу c
                        условием не меньше</label></td>
                <td><input type="a_filter_rating_cond" value="top 33%"/></td>
            </tr>
            <tr>
                <td valign="top" width="400"><input type="checkbox" name="a_filter_cat"/><label>категории c
                        условием</label></td>
                <td><input type="a_filter_cat_cond" value="все категории"/></td>
            </tr>

            <tr>
                <td colspan="2">
                    <hr/>
                </td>
            </tr>
            <tr class="rating_param">
                <td valign="top" width="400"><label>где b = </label></td>
                <td>
                    <select name="func_b">
                        <option value="Sum_R">Сумма рейтинга</option>
                        <option value="count" selected>Количество</option>
                        <option value="mean_R">Средний рейтинг</option>
                    </select>
                    <select type="text" name="b">
                        <option value="articles" selected>Статей</option>
                        <option value="authors">Авторов</option>
                        <option value="comments">Комментариев</option>
                        <option value="votes">Оценок</option>
                        <option value="cats">Категории</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td valign="top" width="400" colspan="2"><label>, отфильтрованных по:</label></td>

            </tr>
            <tr>
                <td valign="top" width="400"><input type="checkbox" name="a_filter_author" checked/><label>автору c
                        условием</label></td>
                <td><input type="a_filter_author_cond" value="данный автор"/></td>
            </tr>
            <tr>
                <td valign="top" width="400"><input type="checkbox" name="a_filter_date" checked/><label>дате публикации
                        c условием не старше</label></td>
                <td><input type="a_filter_date_cond" value="90"/>дней</td>
            </tr>
            <tr>
                <td valign="top" width="400"><input type="checkbox" name="a_filter_rating" checked/><label>рейтингу c
                        условием не меньше</label></td>
                <td><input type="a_filter_rating_cond" value="top 33%"/></td>
            </tr>
            <tr>
                <td valign="top" width="400"><input type="checkbox" name="a_filter_cat"/><label>категории c
                        условием</label></td>
                <td><input type="a_filter_cat_cond" value="все категории"/></td>
            </tr>

            <tr>
                <td colspan="2">
                    <hr/>
                </td>
            </tr>
            <tr class="rating_param">
                <td valign="top" width="400"><label>где c = </label></td>
                <td>
                    <select name="func_c">
                        <option value="Sum_R">Сумма рейтинга</option>
                        <option value="count">Количество</option>
                        <option value="mean_R" selected>Средний рейтинг</option>
                    </select>
                    <select type="text" name="c">
                        <option value="articles">Статей</option>
                        <option value="authors" selected>Авторов</option>
                        <option value="comments">Комментариев</option>
                        <option value="votes">Оценок</option>
                        <option value="cats">Категории</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td valign="top" width="400" colspan="2"><label>, отфильтрованных по:</label></td>

            </tr>
            <tr>
                <td valign="top" width="400"><input type="checkbox" name="c_filter_author" checked/><label>автору c
                        условием</label></td>
                <td><input type="c_filter_author_cond" value="пишущий автор"/></td>
            </tr>
            <tr>
                <td valign="top" width="400"><input type="checkbox" name="с_filter_date" checked/><label>дате публикации
                        c условием не старше</label></td>
                <td><input type="с_filter_date_cond" value="90"/>дней</td>
            </tr>
            <tr>
                <td valign="top" width="400"><input type="checkbox" name="с_filter_rating" checked/><label>рейтингу c
                        условием не меньше</label></td>
                <td><input type="с_filter_rating_cond" value="top 33%"/></td>
            </tr>
            <tr>
                <td valign="top" width="400"><input type="checkbox" name="с_filter_cat"/><label>категории c
                        условием</label></td>
                <td><input type="с_filter_cat_cond" value="все категории"/></td>
            </tr>
            <tr>
                <td colspan="2">
                    <hr/>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="rating_add_param"><a href="#">Добавить параметр</a></div>
                </td>
            </tr>

        </table>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Публикация Статьи</h3>
    </div>
    <div class="panel-body">
        <div>Количество разрешенных публикаций для автора рассчитывается так:</div>
        <table border="0" class="rating-form">
            <tr>
                <td valign="top" width="400"><label>Максимальное количество публикаций в день:</label></td>
                <td><input type="text" name="publish_MTA_amount" value="10"/></td>
            </tr>
            <tr>
                <td valign="top"><label>Пороговое значение<br/>рейтинга автора (ПТА):</label></td>
                <td valign="top">


                    <input type="checkbox" name="publish_use_meanPA"/>средний рейтинг по пишущим авторам
                </td>
            </tr>


            <tr>
                <td valign="top"><label>Если рейтинг автора меньше ПТА, то количество разрешенных публикаций равно</td>
                <td><input type="text" name="publish_less_PTA" value="1"/></td>
            </tr>
            <tr>
                <td valign="top"><label>Если рейтинг автора >= ПТА, то количество разрешенных публикаций равно</td>
                <td><input type="text" name="publish_more_PTA" value="1 + alpha*(RA-PTA)"/></td>

            </tr>
            <tr>
                <td valign="top"><label>где alpha=</td>
                <td><input type="text" name="publish_alpha" value="1"/></td>

            </tr>

        </table>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Публикация комментария</h3>
    </div>
    <div class="panel-body">
        <div>Количество разрешенных комментариев для автора рассчитывается так:</div>
        <table border="0" class="rating-form">
            <tr>
                <td valign="top" width="400"><label>Максимальное количество комментариев в день:</label></td>
                <td><input type="text" name="comment_MTA_amount" value="1200"/></td>
            </tr>
            <tr>
                <td valign="top"><label>Пороговое значение<br/>рейтинга автора (ПТА):</label></td>
                <td valign="top">


                    <input type="checkbox" name="comment_use_meanPA"/>средний рейтинг по пишущим авторам<br/><br/>
                    <input type="checkbox" name="comment_use_top" checked/>нижняя граница топа   <input type="text"
                                                                                                    name="comment_top_PTA"
                                                                                                    size="4"
                                                                                                    value="33%"/><br/><br/>
                    <input type="checkbox" name="comment_only_PA" checked/>только пишущие авторы
                </td>
            </tr>


            <tr>
                <td valign="top"><label>Если рейтинг автора меньше ПТА, то количество разрешенных публикаций равно</td>
                <td><input type="text" name="comment_less_PTA" value="0"/></td>
            </tr>
            <tr>
                <td valign="top"><label>Если рейтинг автора >= ПТА, то количество разрешенных публикаций равно</td>
                <td><input type="text" name="comment_more_PTA" value="alpha*(RA-PTA)"/></td>

            </tr>
            <tr>
                <td valign="top"><label>где alpha=</td>
                <td><input type="text" name="publish_alpha" value="1"/></td>

            </tr>

        </table>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Оценка статьи</h3>
    </div>
    <div class="panel-body">
        <div>Разрешена только авторам, которые:</div>
        <table border="0" class="rating-form">
            <tr>
                <td valign="top" width="400"><label>Входят в топ:</label></td>
                <td><input type="text" name="top" value="33%"/></td>
            </tr>
            <tr>
                <td valign="top"><label>Пороговое значение<br/>рейтинга автора (ПТА):</label></td>
                <td valign="top">


                    <input type="checkbox" name="use_meanPA"/>средний рейтинг по пишущим авторам
                </td>
            </tr>

            <tr>
                <td valign="top"></td>
                <td><input type="checkbox" name="comment_only_PA" checked/>только пишущие авторы</td>
            </tr>

        </table>
    </div>
</div>


<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Оценка комментария</h3>
    </div>
    <div class="panel-body">
        <div>Разрешена только авторам, которые:</div>
        <table border="0" class="rating-form">
            <tr>
                <td valign="top" width="400"><label>Входят в топ:</label></td>
                <td><input type="text" name="top" value="33%"/></td>
            </tr>
            <tr>
                <td valign="top"><label>Пороговое значение<br/>рейтинга автора (ПТА):</label></td>
                <td valign="top">


                    <input type="checkbox" name="use_meanPA"/>средний рейтинг по пишущим авторам
                </td>
            </tr>

            <tr>
                <td valign="top"></td>
                <td><input type="checkbox" name="comment_only_PA" checked/>только пишущие авторы</td>
            </tr>

        </table>
    </div>
</div>


</form>
</div>
