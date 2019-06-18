<?php

namespace app\commands;

date_default_timezone_set('Asia/Yekaterinburg');

use app\models\Graph;
use app\models\Medicine;
use app\models\MedicineItem;
use app\models\Notification;
use app\models\User;
use app\models\UserPreferences;
use Yii;
use yii\console\Controller;


class NotifyController extends Controller
{
    private $url = 'https://fcm.googleapis.com/fcm/send';
    private $YOUR_API_KEY = 'AAAAlzm25OU:APA91bG_u8JNrOnuuCGb2H2fuNyfiRi7U6xdgwZZngxwBazJr2l9_3yPE-8tiIwWbZdwYsB09wWAaW1j8PS_ywgAiRL5Q12Q_PMTDiCpQvYiZIi3jbgA-_MGJ_PfLOM1jflOGDY3Ez3f'; // Server key
    //
    //
    public function actionSend() {
        //отправка push

        //теперь выбираем события для отправки
        //текущее время
        $dt = date('Y-m-d H:i');
        $dataprovider = Graph::find()->where(['plan_take_date' => $dt])
            ->join('LEFT JOIN', 'course', 'course.course_id = graph.course_id')
            ->join('LEFT JOIN', 'user_preferences', 'user_preferences.user_id = course.user_id')
            ->join('LEFT JOIN', 'user', 'user.id = user_preferences.user_id')
            ->select(['graph_id as graph_id', 'firebase_token as firebase_token', 'user.email',
            ])->asArray()->all();

        foreach ($dataprovider as &$graph) {
            //для каждого попавшего - отправляем уведомление
            $YOUR_TOKEN_ID = $graph['firebase_token']; // Client token id

            $msg = array
            (
                'title' => 'MedChest',
                'body' => 'Нужно принять лекарство',
                'icon' => '/red_cross_a.png',
                'click_action' => 'https://google.com',
                'url' => 'https://google.com'
            );

            $notification = array('notification' => $msg);

            $fields = array
            (
                'to' => $YOUR_TOKEN_ID,
                'data' => $notification
            );

            $request_headers = [
                'Content-Type: application/json',
                'Authorization: key=' . $this->YOUR_API_KEY,
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->url);
            curl_setopt( $ch,CURLOPT_POST, true );
            curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $response = curl_exec($ch);
            curl_close($ch);

            //отправка email
            Yii::$app->mailer->compose()
                ->setFrom('notify@medchest.com')
                ->setTo($graph['email'])
                ->setSubject('Уведомление о приеме лекарства')
                ->setTextBody('Текст сообщения')
                ->setHtmlBody('<b>Необходимо принять лекарство!</b>')
                ->send();
        }
    }

    public function actionSendExpired()
    {
        $dataprovider = MedicineItem::find()->all();

        foreach ($dataprovider as &$item)
        {
            if($item->isExpiring(date('Y-m-d')))
            {
                //проверяем не прислали ли уже по нему об истечении срока
                $cnt = Notification::find()->where(['medicine_item_id' => $item->medicine_item_id, 'notification_type_id' => 1])->count();
                if($cnt == 0)
                {
                    $dbuser = User::find()->where(['id' => $item->user_id])->one();
                    $med = Medicine::find()->where(['medicine_id' => $item->medicine_id])->one();
                    //отправка email
                    Yii::$app->mailer->compose()
                        ->setFrom('notify@medchest.com')
                        ->setTo($dbuser->email)
                        ->setSubject('Уведомление об истечении срока годности лекарства')
                        ->setTextBody('Текст сообщения')
                        ->setHtmlBody('<b>Заканчивается срок годности лекарства : '.$med->name.'</b>')
                        ->send();
                    //оствляем запись что вслали по этому препарату
                    $notification = new Notification();
                    $notification->notification_type_id = 1;
                    $notification->medicine_item_id = $item->medicine_item_id;
                    $notification->date_sent = date('Y-m-d H:i:s');
                    $notification->save();
                }
            }
        }
    }

    public function actionSendLow()
    {
        $dataprovider = MedicineItem::find()->all();

        foreach ($dataprovider as &$item)
        {
            $med_id =  $item->medicine_id;
            $pcsCount = Medicine::find()->where(['medicine_id' => $med_id])->one()->pcs;

            $dbuserprefs = UserPreferences::find()->where(['user_id' => $item->user_id])->one();
            if($dbuserprefs == null)
            {
                continue;
            }

            $alertLevel = $dbuserprefs->alert_critical_pcs_left;

            if($item->pcs_left < $pcsCount * ($alertLevel/100))
            {
                $this->actionSendLowLevel($item->user_id, $item->medicine_item_id);
            }
        }
    }

    public function actionSendLowLevel($userId, $medicine_item_id)
    {
        $dbuser = User::find()->where(['id' => $userId])->one();
        $dbuserprefs = UserPreferences::find()->where(['user_id' => $userId])->one();
        $medId = MedicineItem::find()->where(['medicine_item_id' => $medicine_item_id])->one();
        $med = Medicine::find()->where(['medicine_id' => $medId])->one();
        //отправка push

        //для каждого попавшего - отправляем уведомление
        $YOUR_TOKEN_ID = $dbuserprefs->firebase_token; // Client token id

        $msg = array
        (
            'title' => 'MedChest',
            'body' => 'Заканчивается срок действия: '.$med->name,
            'icon' => '/red_cross_a.png',
            'click_action' => 'https://google.com',
            'url' => 'https://google.com'
        );

        $notification = array('notification' => $msg);

        $fields = array
        (
            'to' => $YOUR_TOKEN_ID,
            'data' => $notification
        );

        $request_headers = [
            'Content-Type: application/json',
            'Authorization: key=' . $this->YOUR_API_KEY,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $cnt = Notification::find()->where(['medicine_item_id' => $medicine_item_id, 'notification_type_id' => 2])->count();
        if($cnt == 0) {
            //почта
            //отправка email
            Yii::$app->mailer->compose()
                ->setFrom('notify@medchest.com')
                ->setTo($dbuser->email)
                ->setSubject('Уведомление о низком остатке лекарства')
                ->setTextBody('Текст сообщения')
                ->setHtmlBody('<b>Низкий остаток: ' . $med->name . '</b>')
                ->send();

            //оствляем запись что вслали по этому препарату
            $notification = new Notification();
            $notification->notification_type_id = 2;
            $notification->medicine_item_id = $medicine_item_id;
            $notification->date_sent = date('Y-m-d H:i:s');
            $notification->save();
        }
    }

    public function actionSendTest() {
        //отправка push
        $url = 'https://fcm.googleapis.com/fcm/send';
        $YOUR_API_KEY = 'AAAAlzm25OU:APA91bG_u8JNrOnuuCGb2H2fuNyfiRi7U6xdgwZZngxwBazJr2l9_3yPE-8tiIwWbZdwYsB09wWAaW1j8PS_ywgAiRL5Q12Q_PMTDiCpQvYiZIi3jbgA-_MGJ_PfLOM1jflOGDY3Ez3f'; // Server key
        //теперь выбираем события для отправки
        //текущее время

            //для каждого попавшего - отправляем уведомление
            $YOUR_TOKEN_ID = 'dS5oXMYjLoI:APA91bFTAvkw7uzi1V-n1QC5v79kfExavYqotqn3lHRMx4UrrWSDb5L_Mg97E4by7Pnjg9P5mARUz_LvQUIT8hljteFurE4suJDiczymxpun_TkU3My9z2WErnyIIPkbvHTozSvXIJWg'; // Client token id

            $msg = array
            (
                'title' => 'MedChest',
                'body' => 'Нужно принять лекарство',
                'icon' => 'red_cross_a.png',
                'click_action' => 'https://google.com',
                'url' => 'https://google.com'
            );

            $notification = array('notification' => $msg);

            $fields = array
            (
                'to' => $YOUR_TOKEN_ID,
                'data' => $notification
            );

            $request_headers = [
                'Content-Type: application/json',
                'Authorization: key=' . $YOUR_API_KEY,
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt( $ch,CURLOPT_POST, true );
            curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $response = curl_exec($ch);
            curl_close($ch);
    }


}
