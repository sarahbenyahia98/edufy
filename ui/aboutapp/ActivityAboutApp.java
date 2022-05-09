package com.pixelnx.eacademy.ui.aboutapp;
import android.content.Context;
import android.content.pm.PackageInfo;
import android.content.pm.PackageManager;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;
import com.pixelnx.eacademy.R;
import com.pixelnx.eacademy.ui.base.BaseActivity;
import com.pixelnx.eacademy.utils.widgets.CustomSmallText;
import com.pixelnx.eacademy.utils.widgets.CustomTextExtraBold;

public class ActivityAboutApp extends BaseActivity implements View.OnClickListener {
    Context context;
    ImageView ivBack;
    CustomTextExtraBold tvHeader;
    CustomSmallText versionCode;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_about_app);
        context = getApplicationContext();
        initial();
    }

    void initial() {
        ivBack = findViewById(R.id.ivBack);
        versionCode = findViewById(R.id.versionCode);
        try {
            PackageInfo pInfo = context.getPackageManager().getPackageInfo(getPackageName(), 0);
            String version = pInfo.versionName;
            versionCode.setText(getResources().getString(R.string.app_name)+" \n"+getResources().getString(R.string.Version)+"1.0.0" );
        } catch (PackageManager.NameNotFoundException e) {
            e.printStackTrace();
        }

        tvHeader = findViewById(R.id.tvHeader);
        tvHeader.setText(getResources().getString(R.string.About_App));
        ivBack.setOnClickListener(this);

    }

    @Override
    public void onClick(View view) {
        if (view.getId() == R.id.ivBack) {
            onBackPressed();
        }

    }
}