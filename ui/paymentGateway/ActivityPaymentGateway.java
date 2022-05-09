package com.pixelnx.eacademy.ui.paymentGateway;

import androidx.annotation.Nullable;
import androidx.appcompat.app.AppCompatActivity;

import android.app.Activity;
import android.content.ClipData;
import android.content.ClipboardManager;
import android.content.Context;
import android.content.Intent;
import android.content.res.Configuration;
import android.graphics.Bitmap;
import android.media.MediaScannerConnection;
import android.os.Bundle;
import android.os.Environment;

import android.util.Log;
import android.view.View;
import android.view.ViewTreeObserver;
import android.view.animation.Animation;
import android.view.animation.AnimationUtils;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;
import android.widget.TextView;
import android.widget.Toast;

import com.androidnetworking.AndroidNetworking;
import com.androidnetworking.error.ANError;
import com.androidnetworking.interfaces.ParsedRequestListener;
import com.pixelnx.eacademy.R;
import com.pixelnx.eacademy.model.ModelSettingRecord;
import com.pixelnx.eacademy.model.modellogin.ModelLogin;
import com.pixelnx.eacademy.ui.batch.ModelBachDetails;
import com.pixelnx.eacademy.ui.batch.ModelCatSubCat;
import com.pixelnx.eacademy.ui.home.ActivityHome;
import com.pixelnx.eacademy.ui.multibatch.ActivityMultiBatchHome;
import com.pixelnx.eacademy.utils.AppConsts;
import com.pixelnx.eacademy.utils.ProjectUtils;
import com.pixelnx.eacademy.utils.sharedpref.SharedPref;
import com.pixelnx.eacademy.utils.widgets.CustomSmallText;
import com.pixelnx.eacademy.utils.widgets.CustomTextBold;
import com.pixelnx.eacademy.utils.widgets.CustomTextExtraBold;

import com.pixelnx.eacademy.utils.widgets.CustomeTextRegular;


import java.io.ByteArrayOutputStream;
import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.util.Calendar;
import java.util.Locale;


import static com.pixelnx.eacademy.utils.AppConsts.IS_REGISTER;

public class ActivityPaymentGateway extends AppCompatActivity implements View.OnClickListener {
    ImageView ivBack;
    CustomTextExtraBold tvHeader;
    ImageView copyId, copyPass;
    RelativeLayout paymentDoneLayout;
    CustomTextBold tvEnrollment, tvPassword;
    LinearLayout llLoginDetailsForNewStudents, llPayment;
    static String amountForPayment, BatchId = "", name = "", email = "", mobile = "", token = "", versionCode = "", paymentType = "", amount = "";
    SharedPref sharedPref;
    ModelLogin modelLogin;
    ModelSettingRecord modelSettingRecord;
    Context context;
    static ModelCatSubCat.batchData.SubCategory.BatchData batchData;
    CustomSmallText tvMove;
    static String clientIdPaypal = "", rZPKey = "", tranDoneId = "", stuId = "";
    static String checkLanguage = "";
    static String currencyCode = "";
    TextView tryAgainWhenServerError;
    CustomeTextRegular detailsAfterPaymentDone;
    static boolean directbuy = false;

    @Override

    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_payment_gateway);
        context = ActivityPaymentGateway.this;
        sharedPref = SharedPref.getInstance(context);
        ivBack = findViewById(R.id.ivBack);
        detailsAfterPaymentDone = findViewById(R.id.detailsAfterPaymentDone);
        paymentDoneLayout = findViewById(R.id.paymentDoneLayout);
        tryAgainWhenServerError = findViewById(R.id.tryAgainWhenServerError);
        tryAgainWhenServerError.setOnClickListener(this);
        ivBack.setOnClickListener(this);


        if (getIntent().hasExtra("login")) {
            name = getIntent().getStringExtra("name");
            email = getIntent().getStringExtra("email");
            mobile = getIntent().getStringExtra("mobile");
            versionCode = getIntent().getStringExtra("versionCode");
            token = getIntent().getStringExtra("token");
            initial();
            withOutBatchLogin();


        }else {
            if (getIntent().hasExtra("directbuy")) {

                directbuy = true;
                stuId = getIntent().getStringExtra("stuId");
                BatchId = getIntent().getStringExtra("BatchId");
            }
            try {
                modelSettingRecord = sharedPref.getSettingInfo(AppConsts.APP_INFO);
            } catch (Exception e) {

            }
            if (getIntent().hasExtra("name")) {
                name = getIntent().getStringExtra("name");
                email = getIntent().getStringExtra("email");
                mobile = getIntent().getStringExtra("mobile");
                versionCode = getIntent().getStringExtra("versionCode");
                token = getIntent().getStringExtra("token");
                amount = getIntent().getStringExtra("amount");
                BatchId = getIntent().getStringExtra("BatchId");
                paymentType = getIntent().getStringExtra("paymentType");
            }
            if (getIntent().hasExtra("BatchId")) {
                amount = getIntent().getStringExtra("amount");
                BatchId = getIntent().getStringExtra("BatchId");
                paymentType = getIntent().getStringExtra("paymentType");
            }
            if (getIntent().hasExtra("data")) {

                batchData = (ModelCatSubCat.batchData.SubCategory.BatchData) getIntent().getSerializableExtra("data");
                if (batchData.getBatchType().equals("2")) {
                    if (batchData.getBatchOfferPrice().isEmpty()) {
                        amountForPayment = "" + batchData.getBatchPrice();
                    } else {
                        amountForPayment = "" + batchData.getBatchOfferPrice();
                    }
                } else {
                    amountForPayment = "Free";
                    successSignUpApi("" + BatchId, "", "");
                    initial();
                }
                currencyCode = "" + batchData.getCurrencyCode();

                if (!amountForPayment.equalsIgnoreCase("free")) {

                    if (modelSettingRecord != null) {


                        if (paymentType.equalsIgnoreCase("1")) {
                            //rzp
                            if (!modelSettingRecord.getData().getRazorpayKeyId().isEmpty()) {
                                rZPKey = modelSettingRecord.getData().getRazorpayKeyId();
                                if (!rZPKey.isEmpty()) {
                                    initial();
                                    Intent i = new Intent(this, Razorpay.class);
                                    i.putExtra("rZPKey", "" + rZPKey);
                                    i.putExtra("amount", "" + amountForPayment);
                                    i.putExtra("code", "" + currencyCode);
                                    i.setFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP);
                                    startActivityForResult(i, 111);

                                    //saloni shrivastava
                                } else {
                                    Toast.makeText(context, getResources().getString(R.string.Razorpay_Payment_details_missing_from_admin), Toast.LENGTH_SHORT).show();
                                    finish();
                                }

                            } else {
                                Toast.makeText(context, getResources().getString(R.string.Razorpay_Payment_details_missing_from_admin), Toast.LENGTH_SHORT).show();
                                finish();
                            }

                        } else {
                            //paypal
                            if (!modelSettingRecord.getData().getPaypalClientId().isEmpty()) {
                                initial();
                                clientIdPaypal = modelSettingRecord.getData().getPaypalClientId();
                                if (clientIdPaypal != null) {
                                    if (!clientIdPaypal.isEmpty()) {
                                        Intent i = new Intent(this, paypal.class);
                                        i.putExtra("clientId", "" + clientIdPaypal);
                                        i.putExtra("amount", "" + amountForPayment);
                                        i.putExtra("code", "" + currencyCode);
                                        i.setFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP);
                                        startActivityForResult(i, 000);
                                    } else {
                                        Toast.makeText(context, getResources().getString(R.string.Paypal_Payment_details_missing_from_admin), Toast.LENGTH_SHORT).show();
                                        finish();
                                    }
                                } else {
                                    Toast.makeText(context, getResources().getString(R.string.Paypal_Payment_details_missing_from_admin), Toast.LENGTH_SHORT).show();
                                    finish();
                                }

                            } else {
                                Toast.makeText(context, getResources().getString(R.string.Paypal_Payment_details_missing_from_admin), Toast.LENGTH_SHORT).show();
                                finish();
                            }


                        }

                    } else {
                        siteSettings();
                    }

                }
            }
            if (getIntent().hasExtra("paymentdata")) {
                initial();


            }


            modelLogin = sharedPref.getUser(AppConsts.STUDENT_DATA);
        }


    }


    @Override
    protected void onActivityResult(int requestCode, int resultCode, @Nullable Intent data) {
        super.onActivityResult(requestCode, resultCode, data);


        if (requestCode == 000) {
            if (resultCode == Activity.RESULT_OK) {
                if (!amountForPayment.equalsIgnoreCase("free")) {
                    Toast.makeText(getApplicationContext(), getResources().getString(R.string.Payment_Done), Toast.LENGTH_SHORT).show();
                }
                successSignUpApi("" + BatchId, "" + data.getStringExtra("paymentdata"), "" + data.getStringExtra("amount"));
            }
            if (resultCode == Activity.RESULT_CANCELED) {
                Toast.makeText(context, getResources().getString(R.string.Cancel), Toast.LENGTH_SHORT).show();
            }
        } else {
            if (requestCode == 111) {
                try {
                    successSignUpApi("" + BatchId, "" + data.getStringExtra("paymentdata"), "" + data.getStringExtra("amount"));
                } catch (Exception e) {

                }
                if (resultCode == Activity.RESULT_CANCELED) {

                    finish();
                }
            }
        }
    }

    void siteSettings() {
        ProjectUtils.showProgressDialog(context, false, getResources().getString(R.string.Loading___));
        AndroidNetworking.get(AppConsts.BASE_URL + AppConsts.API_HOMEGENERAL_SETTING).build()
                .getAsObject(ModelSettingRecord.class, new ParsedRequestListener<ModelSettingRecord>() {
                    @Override
                    public void onResponse(ModelSettingRecord response) {
                        ProjectUtils.pauseProgressDialog();
                        if (response.getStatus().equalsIgnoreCase("true")) {

                            sharedPref.setSettingInfo(AppConsts.APP_INFO, response);
                            clientIdPaypal = response.getData().getPaypalClientId();
                            rZPKey = response.getData().getRazorpayKeyId();
                            initial();
                            if (response.getData().getLanguageName().equalsIgnoreCase("arabic")) {
                                //for rtl
                                Configuration configuration = getResources().getConfiguration();
                                configuration.setLayoutDirection(new Locale("fa"));
                                getResources().updateConfiguration(configuration, getResources().getDisplayMetrics());
                                String languageToLoad = "ar"; // your language
                                Locale locale = new Locale(languageToLoad);
                                Locale.setDefault(locale);
                                Configuration config = new Configuration();
                                config.locale = locale;
                                getBaseContext().getResources().updateConfiguration(config,
                                        getBaseContext().getResources().getDisplayMetrics());

                                if (!checkLanguage.equals("ar")) {
                                    recreate();
                                }
                                checkLanguage = "ar";

                            }
                            if (response.getData().getLanguageName().equalsIgnoreCase("french")) {
                                String languageToLoad = "fr"; // your language
                                Locale locale = new Locale(languageToLoad);
                                Locale.setDefault(locale);
                                Configuration config = new Configuration();
                                config.locale = locale;
                                getBaseContext().getResources().updateConfiguration(config,
                                        getBaseContext().getResources().getDisplayMetrics());
                                if (!checkLanguage.equals("fr")) {
                                    recreate();
                                }
                                checkLanguage = "fr";

                            }
                            if (response.getData().getLanguageName().equalsIgnoreCase("english")) {
                                String languageToLoad = "en"; // your language
                                Locale locale = new Locale(languageToLoad);
                                Locale.setDefault(locale);
                                Configuration config = new Configuration();
                                config.locale = locale;
                                getBaseContext().getResources().updateConfiguration(config,
                                        getBaseContext().getResources().getDisplayMetrics());
                                if (!checkLanguage.equals("en")) {
                                    recreate();
                                }
                                checkLanguage = "en";


                            }
                        }

                    }

                    @Override
                    public void onError(ANError anError) {
                        ProjectUtils.pauseProgressDialog();

                    }
                });
    }

    void initial() {
        copyId = findViewById(R.id.copyId);
        copyPass = findViewById(R.id.copyPass);
        tvMove = findViewById(R.id.tvMove);
        tvPassword = findViewById(R.id.tvPassword);
        tvEnrollment = findViewById(R.id.tvEnrollment);
        tvMove.setOnClickListener(this);
        llLoginDetailsForNewStudents = findViewById(R.id.llLoginDetailsForNewStudents);
        llPayment = findViewById(R.id.llPayment);
        tvHeader = findViewById(R.id.tvHeader);
        tvHeader.setText("" + getResources().getString(R.string.app_name));
        copyId.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Animation scaleAnim = AnimationUtils.loadAnimation(context, R.anim.blink_anim);
                copyId.startAnimation(scaleAnim);
                tvEnrollment.startAnimation(scaleAnim);
                ClipboardManager cManager = (ClipboardManager) context.getSystemService(Context.CLIPBOARD_SERVICE);
                ClipData cData = ClipData.newPlainText("text", tvEnrollment.getText().toString());
                cManager.setPrimaryClip(cData);
                Toast.makeText(context, getResources().getString(R.string.Copied_to_clipboard), Toast.LENGTH_SHORT).show();
            }
        });
        copyPass.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Animation scaleAnim = AnimationUtils.loadAnimation(context, R.anim.blink_anim);
                copyPass.startAnimation(scaleAnim);
                tvPassword.startAnimation(scaleAnim);
                ClipboardManager cManager = (ClipboardManager) context.getSystemService(Context.CLIPBOARD_SERVICE);
                ClipData cData = ClipData.newPlainText("text", tvPassword.getText().toString());
                cManager.setPrimaryClip(cData);
                Toast.makeText(context, getResources().getString(R.string.Copied_to_clipboard), Toast.LENGTH_SHORT).show();

            }
        });


    }

    void withOutBatchLogin(){
        tvMove.setVisibility(View.VISIBLE);

        modelLogin = sharedPref.getUser(AppConsts.STUDENT_DATA);
        tvEnrollment.setText("" + getResources().getString(R.string.EnrollmentId) + "   " + modelLogin.getStudentData().getEnrollmentId());
        tvPassword.setText(getResources().getString(R.string.Password) + "    " + modelLogin.getStudentData().getPassword());

        llLoginDetailsForNewStudents.setVisibility(View.VISIBLE);
        llPayment.setVisibility(View.GONE);

        ivBack.setVisibility(View.GONE);
        ProjectUtils.pauseProgressDialog();


    }


    @Override
    protected void onDestroy() {


        super.onDestroy();
    }


    @Override
    public void onClick(View view) {
        switch (view.getId()) {

            case R.id.tvMove:
                startActivity(new Intent(context, ActivityMultiBatchHome.class));
                Toast.makeText(context, getResources().getString(R.string.Welcome) + ", " + modelLogin.getStudentData().getFullName(), Toast.LENGTH_SHORT)
                        .show();
                break;
            case R.id.ivBack:
                onBackPressed();
                break;
            case R.id.tryAgainWhenServerError:
                successSignUpApi("" + BatchId, "" + tranDoneId, amountForPayment);
                break;
        }
    }


    void successSignUpApi(String batchId, String transectionId, String amountt) {

        ProjectUtils.showProgressDialog(context, true, getResources().getString(R.string.Loading___));
        tranDoneId = transectionId;
        if (directbuy) {
            Log.v("saloni1234","saloni1234 directbuy   "+transectionId+"  "+batchId);
            AndroidNetworking.post(AppConsts.BASE_URL + AppConsts.API_CHANGE_BATCH)
                    .addBodyParameter(AppConsts.STUDENT_ID, "" + stuId)
                    .addBodyParameter(AppConsts.BATCH_ID, "" + BatchId)
                    .addBodyParameter(AppConsts.TRANSACTION_ID, "" + transectionId)
                    .addBodyParameter(AppConsts.AMOUNT, "" + amountt)
                    .build().getAsObject(ModelLogin.class, new ParsedRequestListener<ModelLogin>() {
                @Override
                public void onResponse(ModelLogin response) {
                    if (AppConsts.TRUE.equals(response.getStatus())) {
                        sharedPref.setUser(AppConsts.STUDENT_DATA, response);
                        startActivity(new Intent(context, ActivityHome.class));
                        Toast.makeText(context, context.getResources().getString(R.string.BatchAdded), Toast.LENGTH_SHORT).show();
                    } else {
                        Toast.makeText(context, "" + response.getMsg(), Toast.LENGTH_SHORT).show();
                    }
                }

                @Override
                public void onError(ANError anError) {
                    Toast.makeText(context, "" + context.getResources().getString(R.string.Try_again_server_issue), Toast.LENGTH_SHORT).show();

                }
            });
        } else {
            Log.v("saloni1234","with batch idid    "+transectionId+"  "+batchId);
            AndroidNetworking.post(AppConsts.BASE_URL + AppConsts.API_STUDENT_REGISTRATION)
                    .addBodyParameter(AppConsts.NAME, "" + name)
                    .addBodyParameter(AppConsts.EMAIL, "" + email)
                    .addBodyParameter(AppConsts.MOBILE, "" + mobile)
                    .addBodyParameter(AppConsts.TOKEN, "" + token)
                    .addBodyParameter(AppConsts.TRANSACTION_ID, "" + transectionId)
                    .addBodyParameter(AppConsts.AMOUNT, "" + amountForPayment)
                    .addBodyParameter(AppConsts.BATCH_ID, "" + BatchId)
                    .addBodyParameter(AppConsts.VERSION_CODE, "" + versionCode)
                    .build()
                    .getAsObject(ModelLogin.class, new ParsedRequestListener<ModelLogin>() {
                        @Override
                        public void onResponse(ModelLogin response) {
                            Log.v("saloni1234","with batch idid    "+response.getMsg());
                            ProjectUtils.pauseProgressDialog();
                            paymentDoneLayout.setVisibility(View.GONE);
                            if (response.getStatus().equalsIgnoreCase("true")) {
                                tvMove.setVisibility(View.VISIBLE);
                                sharedPref.setUser(AppConsts.STUDENT_DATA, response);
                                sharedPref.setBooleanValue(IS_REGISTER, true);
                                modelLogin = sharedPref.getUser(AppConsts.STUDENT_DATA);
                                tvEnrollment.setText("" + getResources().getString(R.string.EnrollmentId) + "   " + modelLogin.getStudentData().getEnrollmentId());
                                tvPassword.setText(getResources().getString(R.string.Password) + "    " + response.getStudentData().getPassword());
                                llLoginDetailsForNewStudents.setVisibility(View.VISIBLE);
                                llPayment.setVisibility(View.GONE);
                                llLoginDetailsForNewStudents.getViewTreeObserver().addOnGlobalLayoutListener(new ViewTreeObserver.OnGlobalLayoutListener() {

                                    @Override
                                    public void onGlobalLayout() {
                                        llLoginDetailsForNewStudents.setDrawingCacheEnabled(true);
                                        Bitmap myBitmap = llLoginDetailsForNewStudents.getDrawingCache();
                                        saveImage(myBitmap);
                                        Animation scaleAnim = AnimationUtils.loadAnimation(context, R.anim.blink_anim);
                                        llLoginDetailsForNewStudents.startAnimation(scaleAnim);
                                        Toast.makeText(context, getResources().getString(R.string.Screenshot_Captured), Toast.LENGTH_SHORT).show();

                                    }
                                });
                                ivBack.setVisibility(View.GONE);
                            } else {
                                ProjectUtils.pauseProgressDialog();
                                Toast.makeText(context, "" + response.getMsg(), Toast.LENGTH_SHORT).show();
                            }
                        }

                        @Override
                        public void onError(ANError anError) {
                            Toast.makeText(context, getResources().getString(R.string.Try_again), Toast.LENGTH_SHORT).show();
                            try {
                                if (!transectionId.isEmpty()) {
                                    paymentDoneLayout.setVisibility(View.VISIBLE);
                                    detailsAfterPaymentDone.setText(getResources().getString(R.string.PaymentCompleted) + "\n" + getResources().getString(R.string.TransactiondoneId) + "  :  " + transectionId + "\n" + getResources().getString(R.string.PaidAmount) + "  :  " + amountForPayment + " " + batchData.getCurrencyDecimalCode());
                                }
                            } catch (Exception E) {
                            }
                            ProjectUtils.pauseProgressDialog();
                        }
                    });


        }

    }

    public String saveImage(Bitmap myBitmap) {
        ByteArrayOutputStream bytes = new ByteArrayOutputStream();
        myBitmap.compress(Bitmap.CompressFormat.JPEG, 100, bytes);
        File wallpaperDirectory = new File(
                Environment.getExternalStorageDirectory() + "/" + getResources().getString(R.string.app_name));

        if (!wallpaperDirectory.exists()) {
            wallpaperDirectory.mkdirs();
        }

        try {
            File f = new File(wallpaperDirectory, Calendar.getInstance()
                    .getTimeInMillis() + ".jpg");
            f.createNewFile();
            FileOutputStream fo = new FileOutputStream(f);
            fo.write(bytes.toByteArray());
            MediaScannerConnection.scanFile(context,
                    new String[]{f.getPath()},
                    new String[]{"image/jpeg"}, null);
            fo.close();
            return f.getAbsolutePath();
        } catch (IOException e1) {
            e1.printStackTrace();
        }
        return "";
    }

    @Override
    public void onBackPressed() {
        super.onBackPressed();
    }
}